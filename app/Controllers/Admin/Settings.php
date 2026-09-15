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

        return redirect()->to('/admin/settings#identity')->with('success', 'System branding updated successfully.');
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

        return redirect()->to('/admin/settings#themes')->with('success', 'Theme colours updated successfully.');
    }

    /**
     * Update footer information and copyright attribution.
     */
    public function updateFooter()
    {
        if (session()->get('role') !== 'super_admin') {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $model = new SystemSettingModel();
        $fields = [
            'footer_about_title'    => trim($this->request->getPost('footer_about_title') ?? ''),
            'footer_about_text'     => trim($this->request->getPost('footer_about_text') ?? ''),
            'footer_credit'         => trim($this->request->getPost('footer_credit') ?? ''),
            'footer_copyright_text' => trim($this->request->getPost('footer_copyright_text') ?? ''),
        ];

        $model->setMultiple($fields);
        get_all_system_settings(true);

        $this->logActivity('System Settings', 'Updated footer and public information');

        return redirect()->to('/admin/settings#footer')->with('success', 'Footer & public attribution updated successfully.');
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
     * Update background display mode ('slideshow' or 'single').
     */
    public function updateBackgroundMode()
    {
        if (session()->get('role') !== 'super_admin') {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized.']);
        }

        $mode = $this->request->getPost('mode');
        if (! in_array($mode, ['slideshow', 'single'], true)) {
            $mode = 'slideshow';
        }

        $model = new SystemSettingModel();
        $model->setSetting('app_bg_mode', $mode);
        get_all_system_settings(true);

        $this->logActivity('System Settings', 'Switched background display mode to: ' . $mode);

        return $this->response->setJSON([
            'success'    => true,
            'mode'       => $mode,
            'message'    => 'Background mode switched to ' . ($mode === 'slideshow' ? 'Dynamic Rotating Slideshow' : 'Single Hero Background') . '.',
            'csrf_token' => csrf_token(),
            'csrf_hash'  => csrf_hash(),
        ]);
    }

    /**
     * Upload a custom picture for one of the 5 slideshow slots.
     */
    public function uploadSlideshowSlot()
    {
        if (session()->get('role') !== 'super_admin') {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized.']);
        }

        $slot = (int) $this->request->getPost('slot');
        if ($slot < 1 || $slot > 5) {
            return $this->response->setStatusCode(400)->setJSON([
                'success'    => false,
                'message'    => 'Invalid slideshow slot (must be 1-5).',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        $file = $this->request->getFile('image');
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
                'message'    => 'The image exceeds the 8 MB maximum allowed size.',
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
                'message'    => 'Invalid file type. Only PNG, JPG, and WEBP are allowed.',
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

        $settingKey = "app_bg_slideshow_{$slot}";
        $this->deleteOldMedia($settingKey, $uploadDir);

        $ext = $file->guessExtension() ?: 'png';
        $newFilename = "bg_slot_{$slot}_" . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        if (! $file->move($uploadDir, $newFilename)) {
            return $this->response->setStatusCode(500)->setJSON([
                'success'    => false,
                'message'    => 'Failed to save uploaded picture for slot ' . $slot . '.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        $relPath = 'uploads/settings/' . $newFilename;
        $model = new SystemSettingModel();
        $model->setSetting($settingKey, $relPath);
        get_all_system_settings(true);

        $this->logActivity('System Settings', "Uploaded new slideshow background picture for slot {$slot}: {$newFilename}");

        return $this->response->setJSON([
            'success'    => true,
            'slot'       => $slot,
            'message'    => "Slideshow picture for Slot {$slot} updated successfully.",
            'image_url'  => base_url($relPath) . '?v=' . time(),
            'filename'   => $newFilename,
            'csrf_token' => csrf_token(),
            'csrf_hash'  => csrf_hash(),
        ]);
    }

    /**
     * Reset a single slideshow slot back to default artwork.
     */
    public function resetSlideshowSlot()
    {
        if (session()->get('role') !== 'super_admin') {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized.']);
        }

        $slot = (int) $this->request->getPost('slot');
        if ($slot < 1 || $slot > 5) {
            return $this->response->setStatusCode(400)->setJSON([
                'success'    => false,
                'message'    => 'Invalid slideshow slot.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'settings';
        $settingKey = "app_bg_slideshow_{$slot}";
        $this->deleteOldMedia($settingKey, $uploadDir);

        $model = new SystemSettingModel();
        $model->setSetting($settingKey, null);
        get_all_system_settings(true);

        $defaultFiles = [
            1 => 'images/bg/bg1_townhall.png',
            2 => 'images/bg/bg2_aerial_port.png',
            3 => 'images/bg/bg3_aerial_town.png',
            4 => 'images/bg/bg4_terminal_exterior.png',
            5 => 'images/bg/bg5_terminal_bay.png',
        ];

        $this->logActivity('System Settings', "Reset slideshow background picture for slot {$slot} to default");

        return $this->response->setJSON([
            'success'    => true,
            'slot'       => $slot,
            'message'    => "Slideshow slot {$slot} restored to default system artwork.",
            'image_url'  => base_url($defaultFiles[$slot]),
            'filename'   => basename($defaultFiles[$slot]),
            'csrf_token' => csrf_token(),
            'csrf_hash'  => csrf_hash(),
        ]);
    }

    /**
     * Reset all 5 slideshow slots back to system default artworks.
     */
    public function resetAllSlideshow()
    {
        if (session()->get('role') !== 'super_admin') {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized.']);
        }

        $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'settings';
        $model = new SystemSettingModel();

        $defaultFiles = [
            1 => 'images/bg/bg1_townhall.png',
            2 => 'images/bg/bg2_aerial_port.png',
            3 => 'images/bg/bg3_aerial_town.png',
            4 => 'images/bg/bg4_terminal_exterior.png',
            5 => 'images/bg/bg5_terminal_bay.png',
        ];

        for ($i = 1; $i <= 5; $i++) {
            $this->deleteOldMedia("app_bg_slideshow_{$i}", $uploadDir);
            $model->setSetting("app_bg_slideshow_{$i}", null);
        }

        get_all_system_settings(true);

        $this->logActivity('System Settings', 'Reset all 5 slideshow background pictures to defaults');

        return $this->response->setJSON([
            'success'    => true,
            'message'    => 'All 5 slideshow pictures have been restored to system defaults.',
            'defaults'   => array_map(fn($f) => base_url($f), $defaultFiles),
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
