<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SystemSettingModel;

class Settings extends BaseController
{
    /**
     * Display the System Branding & Customization page (Superadmin only).
     */
    public function index()
    {
        if (session()->get('role') !== 'super_admin') {
            return redirect()->back()->with('error', 'You do not have access to this page.');
        }

        $settings = get_all_system_settings(true);

        return view('admin/settings/index', [
            'title'    => 'System Branding & Themes',
            'settings' => $settings,
        ]);
    }

    /**
     * Update core identity settings (name, subtitle, acronym, system title, contact).
     */
    public function updateBranding()
    {
        if (session()->get('role') !== 'super_admin') {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $model = new SystemSettingModel();
        $fields = [
            'app_name'        => trim($this->request->getPost('app_name') ?? ''),
            'app_subtitle'    => trim($this->request->getPost('app_subtitle') ?? ''),
            'acronym'         => trim($this->request->getPost('acronym') ?? ''),
            'system_title'    => trim($this->request->getPost('system_title') ?? ''),
            'contact_phone'   => trim($this->request->getPost('contact_phone') ?? ''),
            'contact_address' => trim($this->request->getPost('contact_address') ?? ''),
            'contact_email'   => trim($this->request->getPost('contact_email') ?? ''),
        ];

        if (empty($fields['app_name'])) {
            return redirect()->back()->withInput()->with('error', 'System name is required.');
        }

        $model->setMultiple($fields);
        get_all_system_settings(true);

        $this->logActivity('System Settings', 'Updated system branding identity (name: ' . $fields['app_name'] . ')');

        return redirect()->to('/admin/settings')->with('success', 'System branding updated successfully.');
    }

    /**
     * Update role theme colours.
     */
    public function updateThemes()
    {
        if (session()->get('role') !== 'super_admin') {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $model = new SystemSettingModel();
        $hexPattern = '/^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/';

        $themeFields = [
            'theme_guest_primary', 'theme_guest_nav_bg', 'theme_guest_nav_text',
            'theme_staff_primary', 'theme_staff_nav_bg', 'theme_staff_nav_text',
            'theme_admin_primary', 'theme_admin_nav_bg', 'theme_admin_nav_text',
        ];

        $updates = [];
        foreach ($themeFields as $field) {
            $value = trim($this->request->getPost($field) ?? '');
            if (! empty($value) && preg_match($hexPattern, $value)) {
                $updates[$field] = $value;
            }
        }

        if (! empty($updates)) {
            $model->setMultiple($updates);
            get_all_system_settings(true);
            $this->logActivity('System Settings', 'Updated role theme colours (' . count($updates) . ' settings)');
        }

        return redirect()->to('/admin/settings')->with('success', 'Theme colours updated successfully.');
    }

    /**
     * Update footer information.
     */
    public function updateFooter()
    {
        if (session()->get('role') !== 'super_admin') {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $model = new SystemSettingModel();
        $fields = [
            'footer_about_title' => trim($this->request->getPost('footer_about_title') ?? ''),
            'footer_about_text'  => trim($this->request->getPost('footer_about_text') ?? ''),
            'footer_credit'      => trim($this->request->getPost('footer_credit') ?? ''),
        ];

        $model->setMultiple($fields);
        get_all_system_settings(true);

        $this->logActivity('System Settings', 'Updated footer information');

        return redirect()->to('/admin/settings')->with('success', 'Footer information updated successfully.');
    }

    /**
     * Upload a custom system logo.
     */
    public function uploadLogo()
    {
        if (session()->get('role') !== 'super_admin') {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized.']);
        }

        $file = $this->request->getFile('logo');

        if (! $file || ! $file->isValid()) {
            $err = $file ? $file->getErrorString() : 'No image file was uploaded.';
            return $this->response->setStatusCode(400)->setJSON([
                'success'    => false,
                'message'    => $err,
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        // Validate size (max 5MB)
        if ($file->getSize() > 5 * 1024 * 1024) {
            return $this->response->setStatusCode(400)->setJSON([
                'success'    => false,
                'message'    => 'The logo exceeds the 5 MB maximum allowed size.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        // Validate MIME
        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/pjpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'];
        $mime = $file->getMimeType();
        if (! in_array(strtolower($mime), $allowedMimes, true)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success'    => false,
                'message'    => 'Invalid file type. Only PNG, JPG, WEBP, GIF, and SVG are allowed.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        // Validate image (skip SVG)
        if ($mime !== 'image/svg+xml') {
            $imageInfo = @getimagesize($file->getTempName());
            if ($imageInfo === false) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success'    => false,
                    'message'    => 'Uploaded file is not a valid image.',
                    'csrf_token' => csrf_token(),
                    'csrf_hash'  => csrf_hash(),
                ]);
            }
        }

        $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'settings';
        if (! is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        // Delete previous custom logo
        $this->deleteOldMedia('app_logo', $uploadDir);

        $ext = $file->guessExtension() ?: 'png';
        $newFilename = 'logo_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        if (! $file->move($uploadDir, $newFilename)) {
            return $this->response->setStatusCode(500)->setJSON([
                'success'    => false,
                'message'    => 'Failed to save uploaded logo.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        $relPath = 'uploads/settings/' . $newFilename;
        $model = new SystemSettingModel();
        $model->setSetting('app_logo', $relPath);
        get_all_system_settings(true);

        $this->logActivity('System Settings', 'Uploaded new system logo: ' . $newFilename);

        return $this->response->setJSON([
            'success'    => true,
            'message'    => 'System logo updated successfully.',
            'image_url'  => base_url($relPath) . '?v=' . time(),
            'csrf_token' => csrf_token(),
            'csrf_hash'  => csrf_hash(),
        ]);
    }

    /**
     * Reset logo to default seal.
     */
    public function resetLogo()
    {
        if (session()->get('role') !== 'super_admin') {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized.']);
        }

        $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'settings';
        $this->deleteOldMedia('app_logo', $uploadDir);

        $model = new SystemSettingModel();
        $model->setSetting('app_logo', null);
        get_all_system_settings(true);

        $this->logActivity('System Settings', 'Reset system logo to default');

        return $this->response->setJSON([
            'success'    => true,
            'message'    => 'Logo reset to default seal.',
            'image_url'  => base_url('images/9HFScgVg_400x400.png'),
            'csrf_token' => csrf_token(),
            'csrf_hash'  => csrf_hash(),
        ]);
    }

    /**
     * Upload a custom system background / hero picture.
     */
    public function uploadBackground()
    {
        if (session()->get('role') !== 'super_admin') {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized.']);
        }

        $file = $this->request->getFile('background');

        if (! $file || ! $file->isValid()) {
            $err = $file ? $file->getErrorString() : 'No image file was uploaded.';
            return $this->response->setStatusCode(400)->setJSON([
                'success'    => false,
                'message'    => $err,
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        // Validate size (max 8MB)
        if ($file->getSize() > 8 * 1024 * 1024) {
            return $this->response->setStatusCode(400)->setJSON([
                'success'    => false,
                'message'    => 'The background exceeds the 8 MB maximum allowed size.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        // Validate MIME
        $allowedMimes = ['image/jpeg', 'image/jpg', 'image/pjpeg', 'image/png', 'image/webp'];
        $mime = $file->getMimeType();
        if (! in_array(strtolower($mime), $allowedMimes, true)) {
            return $this->response->setStatusCode(400)->setJSON([
                'success'    => false,
                'message'    => 'Invalid file type. Only PNG, JPG, and WEBP are allowed for backgrounds.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        $imageInfo = @getimagesize($file->getTempName());
        if ($imageInfo === false) {
            return $this->response->setStatusCode(400)->setJSON([
                'success'    => false,
                'message'    => 'Uploaded file is not a valid image.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'settings';
        if (! is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        // Delete previous custom background
        $this->deleteOldMedia('app_background_image', $uploadDir);

        $ext = $file->guessExtension() ?: 'png';
        $newFilename = 'bg_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        if (! $file->move($uploadDir, $newFilename)) {
            return $this->response->setStatusCode(500)->setJSON([
                'success'    => false,
                'message'    => 'Failed to save uploaded background.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        $relPath = 'uploads/settings/' . $newFilename;
        $model = new SystemSettingModel();
        $model->setSetting('app_background_image', $relPath);
        get_all_system_settings(true);

        $this->logActivity('System Settings', 'Uploaded new system background picture: ' . $newFilename);

        return $this->response->setJSON([
            'success'    => true,
            'message'    => 'System background picture updated successfully.',
            'image_url'  => base_url($relPath) . '?v=' . time(),
            'csrf_token' => csrf_token(),
            'csrf_hash'  => csrf_hash(),
        ]);
    }

    /**
     * Reset background to default artwork.
     */
    public function resetBackground()
    {
        if (session()->get('role') !== 'super_admin') {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized.']);
        }

        $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'settings';
        $this->deleteOldMedia('app_background_image', $uploadDir);

        $model = new SystemSettingModel();
        $model->setSetting('app_background_image', null);
        get_all_system_settings(true);

        $this->logActivity('System Settings', 'Reset system background to default');

        return $this->response->setJSON([
            'success'    => true,
            'message'    => 'Background picture reset to default.',
            'image_url'  => base_url('images/system bg image.png'),
            'csrf_token' => csrf_token(),
            'csrf_hash'  => csrf_hash(),
        ]);
    }

    /**
     * Delete old custom media file if it exists within the settings upload directory.
     */
    private function deleteOldMedia(string $settingKey, string $uploadDir): void
    {
        $currentPath = get_system_setting($settingKey);
        if (! empty($currentPath)) {
            $fullPath = FCPATH . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $currentPath);
            if (is_file($fullPath) && str_starts_with(realpath($fullPath) ?: '', realpath($uploadDir) ?: '')) {
                @unlink($fullPath);
            }
        }
    }
}
