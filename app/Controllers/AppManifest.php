<?php

namespace App\Controllers;

final class AppManifest extends BaseController
{
    public function index()
    {
        $role = $this->request->getGet('role');
        if (!is_string($role) || !in_array($role, ['guest','staff','admin','super_admin'], true)) $role = null;
        $manifest = json_decode(file_get_contents(FCPATH.'manifest.webmanifest'), true, 512, JSON_THROW_ON_ERROR);
        $manifest['theme_color'] = app_browser_theme_color($role);
        return $this->response->setContentType('application/manifest+json')
            ->setHeader('Cache-Control', 'private, no-store')
            ->setBody(json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }
}
