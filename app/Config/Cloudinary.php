<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Cloudinary extends BaseConfig
{
    /**
     * Cloudinary account cloud name.
     */
    public string $cloudName = '';

    /**
     * Cloudinary account API key.
     */
    public string $apiKey = '';

    /**
     * Cloudinary account API secret (stored safely in .env).
     */
    public string $apiSecret = '';

    /**
     * Default parent folder under which system media will be stored.
     */
    public string $folder = 'pttm_uploads';

    /**
     * HTTP connection and transfer timeout in seconds.
     */
    public int $timeout = 15;

    /**
     * Whether Cloudinary upload integration is actively enabled.
     */
    public bool $enabled = true;
}
