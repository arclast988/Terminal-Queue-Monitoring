<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\Files\UploadedFile;
use Psr\Log\LoggerInterface;
use App\Models\LogModel;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        // $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }

    /**
     * Log an activity for the System Activity Log (admin). Safe if logs table missing.
     * Also opportunistically enforces 60-day log retention policy.
     */
    protected function logActivity(string $action, string $details): void
    {
        $userId = session()->get('id');
        if (!$userId && $action !== 'Login') {
            return;
        }
        try {
            $logModel = new LogModel();
            $logModel->insert([
                'user_id'   => $userId,
                'action'    => $action,
                'details'   => $details,
                'timestamp' => date('Y-m-d H:i:s')
            ]);

            // Opportunistically prune logs older than retention period (throttled to run at most every 6 hours)
            if (!cache('rt_logs_last_purged')) {
                $logModel->purgeOldLogs(log_retention_days());
                cache()->save('rt_logs_last_purged', time(), 21600);
            }
        } catch (\Throwable $e) {
            // Ignore if logs table missing or any DB error
        }
    }

    /**
     * Broadcast an update to the PHP WebSocket server.
     */
    protected function broadcastUpdate(string $type, array $data = []): void
    {
        $isPassengerChange = ($data['action'] ?? '') === 'passenger_change';
        // Passenger taps fire very frequently: they are patched in-place via
        // WS/passenger_change + poll counts, so they must NOT bump the global
        // sync token (otherwise every tap would trigger a full list refresh).
        // Structural changes (vehicle add/edit, queue add/status, etc.) bump
        // the token so pollers can detect modal-list changes without WS.
        $now = microtime(true);
        if (! $isPassengerChange) {
            // Atomic sync-token write to avoid partial reads under concurrency.
            $tokenFile = WRITEPATH . 'sync_token.txt';
            $tmpFile   = $tokenFile . '.' . getmypid() . '.tmp';
            if (@file_put_contents($tmpFile, (string) $now, LOCK_EX) !== false) {
                @rename($tmpFile, $tokenFile);
            } else {
                @file_put_contents($tokenFile, (string) $now, LOCK_EX);
            }
        }

        // Invalidate the short-lived public feed caches so this change is
        // reflected on the very next poll instead of waiting out the TTL.
        // Best-effort: the short TTLs already bound any staleness if this fails.
        // Passenger changes only need the queue-status cache cleared; heavier
        // actions (add/remove/status) invalidate all cached public feeds.
        try {
            $cache = cache();
            $cache->delete('rt_queue_status');
            $cache->delete('rt_queue_status_pkg');
            $cache->delete('rt_home_status');
            // Schedules display passenger counts and status, so updates MUST invalidate schedules cache
            try {
                $cache->deleteMatching('rt_sched_*');
            } catch (\Throwable $e) {
                // ignore
            }

            if (! $isPassengerChange) {
                $cache->delete('rt_announcements');
                $cache->delete('rt_announcements_pkg');
                $cache->delete('rt_fares_api');
                $cache->delete('rt_fares_api_v2');
                $cache->delete('db_vehicle_types');
                $cache->delete('db_vehicle_types_v2');
            }
        } catch (\Throwable $e) {
            // ignore — caching is an optimisation, not a correctness requirement
        }

        $msgId = bin2hex(random_bytes(4));
        $payload = json_encode([
            'broadcast_id' => $msgId,
            'sync_token'   => $now,
            'type'         => $type,
            'data'         => $data,
            'timestamp'    => date('Y-m-d H:i:s'),
        ]);

        try {
            $broadcastPort = (int) env('websocket.broadcastPort', 8082);
            // Resilient loopback connect with 200ms timeout
            $fp = @stream_socket_client(
                'tcp://127.0.0.1:' . $broadcastPort,
                $errno,
                $errstr,
                0.20,
                STREAM_CLIENT_CONNECT
            );
            if (! $fp) {
                // Retry once on transient busy loopback
                usleep(10000);
                $fp = @stream_socket_client(
                    'tcp://127.0.0.1:' . $broadcastPort,
                    $errno,
                    $errstr,
                    0.20,
                    STREAM_CLIENT_CONNECT
                );
            }
            if ($fp) {
                stream_set_timeout($fp, 0, 100000);
                $remaining = $payload;
                while ($remaining !== '') {
                    $written = @fwrite($fp, $remaining);
                    if ($written === false || $written === 0) {
                        break;
                    }
                    $remaining = substr($remaining, $written);
                }
                @fclose($fp);
            }
        } catch (\Throwable $e) {
            // Silent fail — polling fallback is always active
        }
    }

    /**
     * Get all active announcements ordered by sort_order.
     */
    protected function getActiveAnnouncements(): array
    {
        $announcementModel = new \App\Models\AnnouncementModel();
        return $announcementModel->where('is_active', 1)->orderBy('sort_order', 'ASC')->findAll();
    }

    /**
     * Compare submitted scalar fields against the stored record.
     * Values are string-normalized; arrays are compared order-insensitively.
     * Returns true when every key in $new matches $old (missing = '').
     */
    protected function inputsUnchanged(array $old, array $new): bool
    {
        foreach ($new as $key => $value) {
            if (is_array($value)) {
                $oldValues = array_map('strval', (array) ($old[$key] ?? []));
                $newValues = array_map('strval', $value);
                sort($oldValues);
                sort($newValues);
                if ($oldValues !== $newValues) {
                    return false;
                }
                continue;
            }
            if ((string) ($old[$key] ?? '') !== (string) $value) {
                return false;
            }
        }
        return true;
    }

    /** Return a safe file extension based on verified image content. */
    protected function validatedImageExtension(UploadedFile $file, bool $allowGif = false): ?string
    {
        $image = @getimagesize($file->getTempName());
        $extensions = [
            IMAGETYPE_JPEG => ['jpg', ['image/jpeg', 'image/jpg', 'image/pjpeg']],
            IMAGETYPE_PNG => ['png', ['image/png', 'image/x-png']],
            IMAGETYPE_WEBP => ['webp', ['image/webp']],
        ];
        if ($allowGif) {
            $extensions[IMAGETYPE_GIF] = ['gif', ['image/gif']];
        }

        $type = $image[2] ?? null;
        if (!isset($extensions[$type])) {
            return null;
        }

        return in_array(strtolower($file->getMimeType()), $extensions[$type][1], true)
            ? $extensions[$type][0]
            : null;
    }

    /**
     * Redirect back with an informational notice when an update form was
     * submitted without any actual changes (no write, log, or broadcast).
     */
    protected function noChangesResponse()
    {
        return redirect()->back()->withInput()->with('info', 'No changes detected — nothing was updated.');
    }

    /**
     * Get a pre-configured Email service instance.
     */
    protected function getConfiguredEmailService(): \CodeIgniter\Email\Email
    {
        /** @var \Config\Email $config */
        $config = clone config('Email');

        $config->SMTPHost = trim($config->SMTPHost);
        $config->SMTPUser = trim($config->SMTPUser);
        $config->SMTPPass = trim($config->SMTPPass);

        // Google displays app passwords in four groups for readability, but
        // SMTP authentication expects the actual 16-character passcode.
        if (strcasecmp($config->SMTPHost, 'smtp.gmail.com') === 0) {
            $config->SMTPPass = (string) preg_replace('/\s+/', '', $config->SMTPPass);
        }

        // Use a fresh instance so a request cannot inherit recipients,
        // reply-to headers, or stale SMTP state from another email.
        $emailSvc = \Config\Services::email($config, false);

        $fromEmail = $config->fromEmail ?: $config->SMTPUser;
        $fromName  = $config->fromName ?: (app_name() . ' System');

        $emailSvc->setFrom($fromEmail, $fromName);
        return $emailSvc;
    }

    /**
     * Send HTML mail through the configured transport. Railway production uses
     * an HTTPS email API because outbound SMTP is unavailable on lower plans.
     * Local installations and SMTP-enabled hosts can continue using SMTP.
     *
     * @param string|string[] $to
     */
    protected function sendConfiguredHtmlEmail(
        string|array $to,
        string $subject,
        string $html,
        ?string $replyToEmail = null,
        ?string $replyToName = null
    ): bool {
        /** @var \Config\Email $config */
        $config = clone config('Email');
        $provider = strtolower(trim($config->deliveryProvider));
        $recipients = $this->normalizeEmailRecipients($to);

        if ($recipients === []) {
            log_message('error', 'Email delivery aborted: no valid recipients were configured.');
            return false;
        }

        if ($provider === 'brevo') {
            $hasBrevo = trim($config->brevoApiKey) !== '' && filter_var(trim($config->fromEmail ?: $config->SMTPUser), FILTER_VALIDATE_EMAIL);
            if ($hasBrevo) {
                return $this->sendViaBrevo($config, $recipients, $subject, $html, $replyToEmail, $replyToName);
            }

            $hasSmtp = trim($config->SMTPUser) !== '' && trim($config->SMTPPass) !== '';
            if ($hasSmtp) {
                log_message('info', 'Brevo API key is not configured; falling back to SMTP.');
            } else {
                return $this->sendViaBrevo($config, $recipients, $subject, $html, $replyToEmail, $replyToName);
            }
        } elseif ($provider === 'smtp') {
            $hasSmtp = trim($config->SMTPUser) !== '' && trim($config->SMTPPass) !== '';
            $hasBrevo = trim($config->brevoApiKey) !== '' && filter_var(trim($config->fromEmail ?: $config->SMTPUser), FILTER_VALIDATE_EMAIL);
            if (!$hasSmtp && $hasBrevo) {
                log_message('info', 'SMTP credentials are not configured; falling back to Brevo API.');
                return $this->sendViaBrevo($config, $recipients, $subject, $html, $replyToEmail, $replyToName);
            }
        } elseif ($provider !== '') {
            log_message('error', 'Email delivery aborted: unsupported provider "' . $provider . '".');
            return false;
        }

        try {
            $emailSvc = $this->getConfiguredEmailService();
            $emailSvc->setTo($recipients);
            $emailSvc->setSubject($subject);
            $emailSvc->setMessage($html);
            if ($replyToEmail !== null && filter_var($replyToEmail, FILTER_VALIDATE_EMAIL)) {
                $emailSvc->setReplyTo($replyToEmail, $replyToName ?? '');
            }

            if ($emailSvc->send()) {
                return true;
            }

            log_message('error', 'SMTP email delivery failed: ' . $emailSvc->printDebugger(['headers']));
        } catch (\Throwable $exception) {
            log_message('error', 'SMTP email delivery threw an exception: ' . $exception->getMessage());
        }

        return false;
    }

    /**
     * @param string[] $recipients
     */
    protected function sendViaBrevo(
        \Config\Email $config,
        array $recipients,
        string $subject,
        string $html,
        ?string $replyToEmail,
        ?string $replyToName
    ): bool {
        $apiKey = trim($config->brevoApiKey);
        $fromEmail = trim($config->fromEmail ?: $config->SMTPUser);

        if ($apiKey === '' || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            log_message('error', 'Brevo email delivery is not configured. Set BREVO_API_KEY and a valid EMAIL_FROM.');
            return false;
        }

        $payload = [
            'sender' => [
                'email' => $fromEmail,
                'name' => trim($config->fromName ?: app_name()),
            ],
            'to' => array_map(static fn(string $email): array => ['email' => $email], $recipients),
            'subject' => $subject,
            'htmlContent' => $html,
        ];

        if ($replyToEmail !== null && filter_var($replyToEmail, FILTER_VALIDATE_EMAIL)) {
            $payload['replyTo'] = [
                'email' => $replyToEmail,
                'name' => trim($replyToName ?? ''),
            ];
        }

        try {
            $response = \Config\Services::curlrequest()->post($config->brevoApiUrl, [
                'headers' => [
                    'accept' => 'application/json',
                    'api-key' => $apiKey,
                    'content-type' => 'application/json',
                ],
                'json' => $payload,
                'timeout' => max(2, $config->apiTimeout),
                'http_errors' => false,
            ]);
            $statusCode = $response->getStatusCode();
            if ($statusCode >= 200 && $statusCode < 300) {
                return true;
            }

            log_message('error', 'Brevo email delivery failed with HTTP status ' . $statusCode . '.');
        } catch (\Throwable $exception) {
            log_message('error', 'Brevo email delivery threw an exception: ' . $exception->getMessage());
        }

        return false;
    }

    /**
     * @param string|string[] $to
     * @return string[]
     */
    private function normalizeEmailRecipients(string|array $to): array
    {
        $values = is_array($to) ? $to : preg_split('/[,;]+/', $to);
        if (!is_array($values)) {
            return [];
        }

        $valid = [];
        foreach ($values as $value) {
            $email = trim((string) $value);
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $valid[$email] = $email;
            }
        }

        return array_values($valid);
    }
}
