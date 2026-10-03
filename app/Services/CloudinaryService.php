<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;
use Config\Cloudinary as CloudinaryConfig;

class CloudinaryService
{
    protected string $cloudName;
    protected string $apiKey;
    protected string $apiSecret;
    protected string $defaultFolder;
    protected int $timeout;
    protected bool $enabled;

    public function __construct(?CloudinaryConfig $config = null)
    {
        $config ??= config('Cloudinary');

        $this->cloudName     = trim($config->cloudName ?: (string) env('cloudinary.cloudName', ''));
        $this->apiKey        = trim($config->apiKey ?: (string) env('cloudinary.apiKey', ''));
        $this->apiSecret     = trim($config->apiSecret ?: (string) env('cloudinary.apiSecret', ''));
        $this->defaultFolder = trim($config->folder ?: (string) env('cloudinary.folder', 'pttm_uploads'));
        $this->timeout       = max(5, (int) ($config->timeout ?: 15));
        $this->enabled       = (bool) ($config->enabled ?? env('cloudinary.enabled', true));
    }

    /**
     * Check if Cloudinary service is enabled and configured with required credentials.
     */
    public function isConfigured(): bool
    {
        return $this->enabled
            && $this->cloudName !== ''
            && $this->apiKey !== ''
            && $this->apiSecret !== '';
    }

    /**
     * Upload an image to Cloudinary.
     *
     * @param UploadedFile|string $file UploadedFile instance, local file path, or base64 data URI
     * @param string $subFolder Optional sub-folder (e.g., 'vehicles', 'avatars', 'branding')
     * @param string|null $publicId Optional custom public ID
     * @return array|null Returns ['secure_url' => ..., 'public_id' => ..., 'format' => ..., 'bytes' => ...] or null on failure
     */
    public function uploadImage($file, string $subFolder = '', ?string $publicId = null): ?array
    {
        if (! $this->isConfigured()) {
            log_message('warning', 'Cloudinary upload skipped: service is not configured or disabled.');
            return null;
        }

        $folder = trim($this->defaultFolder, '/');
        if ($subFolder !== '') {
            $folder .= '/' . trim($subFolder, '/');
        }

        $timestamp = time();
        $params = [
            'folder'    => $folder,
            'timestamp' => $timestamp,
        ];

        if (! empty($publicId)) {
            // Strip any extension if included in public_id
            $params['public_id'] = pathinfo($publicId, PATHINFO_FILENAME);
        }

        $signature = $this->generateSignature($params);

        $postData = $params;
        $postData['api_key']   = $this->apiKey;
        $postData['signature'] = $signature;

        if ($file instanceof UploadedFile) {
            if (! $file->isValid()) {
                log_message('error', 'Cloudinary upload aborted: uploaded file is invalid.');
                return null;
            }
            $postData['file'] = new \CURLFile($file->getTempName(), $file->getMimeType(), $file->getClientName());
        } elseif (is_string($file)) {
            if (str_starts_with($file, 'data:image/')) {
                $postData['file'] = $file;
            } elseif (is_file($file)) {
                $mime = mime_content_type($file) ?: 'application/octet-stream';
                $postData['file'] = new \CURLFile($file, $mime, basename($file));
            } else {
                log_message('error', 'Cloudinary upload aborted: file path does not exist: ' . $file);
                return null;
            }
        } else {
            log_message('error', 'Cloudinary upload aborted: unsupported file payload type.');
            return null;
        }

        $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/upload";
        $response = $this->executePost($url, $postData);

        if ($response && ! empty($response['secure_url'])) {
            return [
                'secure_url' => $response['secure_url'],
                'public_id'  => $response['public_id'] ?? ($postData['public_id'] ?? ''),
                'format'     => $response['format'] ?? '',
                'bytes'      => $response['bytes'] ?? 0,
            ];
        }

        return null;
    }

    /**
     * Upload an existing local file from the server.
     */
    public function uploadLocalFile(string $filePath, string $subFolder = '', ?string $publicId = null): ?array
    {
        if (! is_file($filePath)) {
            log_message('error', 'Cloudinary uploadLocalFile failed: file does not exist: ' . $filePath);
            return null;
        }

        return $this->uploadImage($filePath, $subFolder, $publicId);
    }

    /**
     * Delete an image from Cloudinary by its public ID or full Cloudinary URL.
     */
    public function deleteImage(string $publicIdOrUrl): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $publicId = $this->extractPublicIdFromUrl($publicIdOrUrl);
        if (empty($publicId)) {
            return false;
        }

        $timestamp = time();
        $params = [
            'public_id' => $publicId,
            'timestamp' => $timestamp,
        ];
        $signature = $this->generateSignature($params);

        $postData = [
            'public_id' => $publicId,
            'timestamp' => $timestamp,
            'api_key'   => $this->apiKey,
            'signature' => $signature,
        ];

        $url = "https://api.cloudinary.com/v1_1/{$this->cloudName}/image/destroy";
        $response = $this->executePost($url, $postData);

        return ($response['result'] ?? '') === 'ok';
    }

    /**
     * Extract the Cloudinary public_id from a full Cloudinary URL.
     * E.g. "https://res.cloudinary.com/demo/image/upload/v12345/pttm_uploads/vehicles/veh_1.jpg"
     * -> "pttm_uploads/vehicles/veh_1"
     */
    public function extractPublicIdFromUrl(string $url): string
    {
        $url = trim($url);
        if (! str_starts_with($url, 'http://') && ! str_starts_with($url, 'https://')) {
            // Already a public_id
            return $url;
        }

        // Must be a Cloudinary URL
        if (! str_contains($url, 'cloudinary.com')) {
            return '';
        }

        // Match after /upload/ (skipping optional transformation segments and /v\d+/)
        if (preg_match('#/image/upload/(?:[^/]+/)*(?:v\d+/)?([^?\#]+)#', $url, $matches)) {
            $pathWithExt = $matches[1];
            // Remove file extension
            $pathParts = pathinfo($pathWithExt);
            $dir = $pathParts['dirname'] !== '.' ? $pathParts['dirname'] . '/' : '';
            return $dir . $pathParts['filename'];
        }

        return '';
    }

    /**
     * Generate standard Cloudinary SHA-1 authentication signature.
     */
    public function generateSignature(array $params): string
    {
        // Exclude binary payload, API key, signature, etc.
        $excluded = ['file', 'cloud_name', 'resource_type', 'api_key', 'signature'];
        $signable = [];
        foreach ($params as $k => $v) {
            if (! in_array($k, $excluded, true) && $v !== null && $v !== '') {
                $signable[$k] = (string) $v;
            }
        }

        ksort($signable);

        $pairs = [];
        foreach ($signable as $k => $v) {
            $pairs[] = "{$k}={$v}";
        }

        $toSign = implode('&', $pairs) . $this->apiSecret;
        return sha1($toSign);
    }

    /**
     * Perform HTTP POST request to Cloudinary API.
     */
    protected function executePost(string $endpoint, array $postData): ?array
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($endpoint);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

            $rawResponse = curl_exec($ch);
            $httpCode    = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError   = curl_error($ch);
            curl_close($ch);

            if ($rawResponse === false || $httpCode < 200 || $httpCode >= 300) {
                log_message('error', "Cloudinary API request failed (HTTP {$httpCode}): {$curlError} - Body: {$rawResponse}");
                return null;
            }

            $decoded = json_decode($rawResponse, true);
            return is_array($decoded) ? $decoded : null;
        }

        // Fallback to CodeIgniter CURLRequest if curl_init is not directly available
        try {
            $client = \Config\Services::curlrequest([
                'timeout' => $this->timeout,
            ]);
            $response = $client->post($endpoint, [
                'multipart'   => $postData,
                'http_errors' => false,
            ]);

            $code = $response->getStatusCode();
            $body = (string) $response->getBody();
            if ($code >= 200 && $code < 300) {
                $decoded = json_decode($body, true);
                return is_array($decoded) ? $decoded : null;
            }

            log_message('error', "Cloudinary CURLRequest failed (HTTP {$code}): {$body}");
        } catch (\Throwable $e) {
            log_message('error', 'Cloudinary request exception: ' . $e->getMessage());
        }

        return null;
    }
}
