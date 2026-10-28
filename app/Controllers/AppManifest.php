<?php

namespace App\Controllers;

final class AppManifest extends BaseController
{
    public function index()
    {
        $manifest = json_decode(file_get_contents(APPPATH.'Config/app.webmanifest'), true, 512, JSON_THROW_ON_ERROR);
        // Older installations can retain ?role=guest; use the current session.
        $manifest['theme_color'] = app_browser_theme_color();
        return $this->response->setContentType('application/manifest+json')
            ->setHeader('Cache-Control', 'private, no-store')
            ->setBody(json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }
}
