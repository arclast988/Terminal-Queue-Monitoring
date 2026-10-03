<?php

namespace Tests\Unit;

use App\Services\CloudinaryService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Cloudinary as CloudinaryConfig;

final class CloudinaryServiceTest extends CIUnitTestCase
{
    public function testExtractPublicIdFromUrl(): void
    {
        $service = new CloudinaryService();

        $url = 'https://res.cloudinary.com/pkimfmqw/image/upload/v1727938492/pttm_uploads/vehicles/veh_abc123.jpg';
        $this->assertSame('pttm_uploads/vehicles/veh_abc123', $service->extractPublicIdFromUrl($url));

        $urlWithTransform = 'https://res.cloudinary.com/pkimfmqw/image/upload/w_200,h_200,c_fill/v1727938492/pttm_uploads/avatars/avatar_1.png';
        $this->assertSame('pttm_uploads/avatars/avatar_1', $service->extractPublicIdFromUrl($urlWithTransform));

        $plainId = 'pttm_uploads/vehicles/veh_test';
        $this->assertSame('pttm_uploads/vehicles/veh_test', $service->extractPublicIdFromUrl($plainId));
    }

    public function testSignatureGeneration(): void
    {
        $config = new CloudinaryConfig();
        $config->cloudName = 'test_cloud';
        $config->apiKey    = '12345';
        $config->apiSecret = 'secret_abc';
        $service = new CloudinaryService($config);

        $params = [
            'timestamp' => 1700000000,
            'folder'    => 'pttm_uploads/vehicles',
            'public_id' => 'veh_test_1',
        ];

        // Alphabetical order: folder=pttm_uploads/vehicles&public_id=veh_test_1&timestamp=1700000000secret_abc
        $expectedString = 'folder=pttm_uploads/vehicles&public_id=veh_test_1&timestamp=1700000000secret_abc';
        $expectedSignature = sha1($expectedString);

        $this->assertSame($expectedSignature, $service->generateSignature($params));
    }

    public function testIsConfiguredReturnsTrueWhenCredentialsSet(): void
    {
        $config = new CloudinaryConfig();
        $config->cloudName = 'test_cloud';
        $config->apiKey    = '12345';
        $config->apiSecret = 'secret_abc';
        $config->enabled   = true;
        $service = new CloudinaryService($config);
        $this->assertTrue($service->isConfigured());

        $disabledConfig = clone $config;
        $disabledConfig->enabled = false;
        $disabledService = new CloudinaryService($disabledConfig);
        $this->assertFalse($disabledService->isConfigured());
    }

    public function testMediaUrlHelper(): void
    {
        $this->assertTrue(function_exists('media_url'));

        // Empty returns empty string
        $this->assertSame('', media_url(null));
        $this->assertSame('', media_url(''));

        // Remote HTTPS Cloudinary URL returns untouched
        $cloudinaryUrl = 'https://res.cloudinary.com/pkimfmqw/image/upload/v12345/pttm_uploads/vehicles/veh_1.jpg';
        $this->assertSame($cloudinaryUrl, media_url($cloudinaryUrl));

        // Local relative path gets prefixed with base_url
        $localPath = 'uploads/vehicles/veh_test.jpg';
        $this->assertSame(base_url($localPath), media_url($localPath));

        // Leading slashes/backslashes normalized
        $slashPath = '/uploads/avatars/avatar_1.png';
        $this->assertSame(base_url('uploads/avatars/avatar_1.png'), media_url($slashPath));
    }
}

