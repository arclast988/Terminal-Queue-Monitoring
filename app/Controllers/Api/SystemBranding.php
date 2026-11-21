<?php

namespace App\Controllers\Api;

use CodeIgniter\Controller;

/** Read-only public artwork settings for pages whose live connection is unavailable. */
class SystemBranding extends Controller
{
    public function index()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        return $this->response
            ->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->setJSON([
                'success' => true,
                'data' => [
                    'category' => 'background',
                    'app_bg_mode' => app_bg_mode(),
                    'app_background_image' => app_bg_image(),
                    'app_bg_slideshow' => array_values(app_bg_slideshow()),
                ],
            ]);
    }
}
