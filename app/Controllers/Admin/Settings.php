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
            'title'    => 'System Themes',
            'settings' => $settings,
        ]);
    }

    /**
     * Update core identity settings (name, subtitle, acronym, system title, contact).
     */
    public function updateBranding()
    {
        if (session()->get('role') !== 'super_admin') {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized.']);
            }
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $model = new SystemSettingModel();
        $fields = [
            'app_name'              => trim($this->request->getPost('app_name') ?? ''),
            'app_subtitle'          => trim($this->request->getPost('app_subtitle') ?? ''),
            'acronym'               => trim($this->request->getPost('acronym') ?? ''),
            'system_title'          => trim($this->request->getPost('system_title') ?? ''),
            'contact_phone'         => trim($this->request->getPost('contact_phone') ?? ''),
            'contact_address'       => trim($this->request->getPost('contact_address') ?? ''),
            'contact_email'         => trim($this->request->getPost('contact_email') ?? ''),
            'login_headline'        => trim($this->request->getPost('login_headline') ?? ''),
            'login_subheadline'     => trim($this->request->getPost('login_subheadline') ?? ''),
            'login_kicker'          => trim($this->request->getPost('login_kicker') ?? ''),
            'login_feature1_title'  => trim($this->request->getPost('login_feature1_title') ?? ''),
            'login_feature1_desc'   => trim($this->request->getPost('login_feature1_desc') ?? ''),
            'login_feature2_title'  => trim($this->request->getPost('login_feature2_title') ?? ''),
            'login_feature2_desc'   => trim($this->request->getPost('login_feature2_desc') ?? ''),
        ];

        if (empty($fields['app_name'])) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success'    => false,
                    'message'    => 'System name is required.',
                    'csrf_token' => csrf_token(),
                    'csrf_hash'  => csrf_hash(),
                ]);
            }
            return redirect()->back()->withInput()->with('error', 'System name is required.');
        }

        $currentSettings = get_all_system_settings();
        $hasChange = false;
        foreach ($fields as $k => $v) {
            if (trim((string) ($currentSettings[$k] ?? '')) !== $v) {
                $hasChange = true;
                break;
            }
        }
        if (! $hasChange) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success'    => true,
                    'no_change'  => true,
                    'message'    => 'No changes detected — system identity is already up to date.',
                    'csrf_token' => csrf_token(),
                    'csrf_hash'  => csrf_hash(),
                ]);
            }
            return $this->noChangesResponse();
        }

        $model->setMultiple($fields);
        get_all_system_settings(true);
        $this->broadcastBrandingChange('identity');

        $this->logActivity('System Settings', 'Updated system themes & identity (name: ' . $fields['app_name'] . ')');

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success'    => true,
                'message'    => 'System identity & themes updated successfully.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
                'data'       => app_full_branding_payload('identity'),
            ]);
        }

        return redirect()->to('/admin/settings#identity')->with('success', 'System identity & themes updated successfully.');
    }

    /**
     * Update role theme colours.
     */
    public function updateThemes()
    {
        if (session()->get('role') !== 'super_admin') {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized.']);
            }
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

        $currentSettings = get_all_system_settings();
        $hasThemeChange = false;
        foreach ($updates as $k => $v) {
            if (strtolower(trim((string) ($currentSettings[$k] ?? ''))) !== strtolower($v)) {
                $hasThemeChange = true;
                break;
            }
        }
        if (! $hasThemeChange || empty($updates)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success'    => true,
                    'no_change'  => true,
                    'message'    => 'No changes detected — theme colours are already up to date.',
                    'csrf_token' => csrf_token(),
                    'csrf_hash'  => csrf_hash(),
                ]);
            }
            return $this->noChangesResponse();
        }

        $model->setMultiple($updates);
        get_all_system_settings(true);
        $this->broadcastBrandingChange('theme');
        $this->logActivity('System Settings', 'Updated role theme colours (' . count($updates) . ' settings)');

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success'    => true,
                'message'    => 'Theme colours updated successfully.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
                'updates'    => $updates,
                'data'       => app_full_branding_payload('theme'),
            ]);
        }

        return redirect()->to('/admin/settings#themes')->with('success', 'Theme colours updated successfully.');
    }

    /**
     * Update footer information and copyright attribution.
     */
    public function updateFooter()
    {
        if (session()->get('role') !== 'super_admin') {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized.']);
            }
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $model = new SystemSettingModel();
        $fields = [
            'footer_about_title'    => trim($this->request->getPost('footer_about_title') ?? ''),
            'footer_about_text'     => trim($this->request->getPost('footer_about_text') ?? ''),
            'footer_credit'         => trim($this->request->getPost('footer_credit') ?? ''),
            'footer_copyright_text' => trim($this->request->getPost('footer_copyright_text') ?? ''),
        ];

        $currentSettings = get_all_system_settings();
        $hasFooterChange = false;
        foreach ($fields as $k => $v) {
            if (trim((string) ($currentSettings[$k] ?? '')) !== $v) {
                $hasFooterChange = true;
                break;
            }
        }
        if (! $hasFooterChange) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success'    => true,
                    'no_change'  => true,
                    'message'    => 'No changes detected — footer settings are already up to date.',
                    'csrf_token' => csrf_token(),
                    'csrf_hash'  => csrf_hash(),
                ]);
            }
            return $this->noChangesResponse();
        }

        $model->setMultiple($fields);
        get_all_system_settings(true);
        $this->broadcastBrandingChange('footer');

        $this->logActivity('System Settings', 'Updated footer and public information');

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success'    => true,
                'message'    => 'Footer & public attribution updated successfully.',
                'data'       => app_full_branding_payload('footer'),
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        return redirect()->to('/admin/settings#footer')->with('success', 'Footer & public attribution updated successfully.');
    }

    /**
     * Update operational & data retention settings (log retention, departure retention, vehicle queue cooldown).
     */
    public function updateOperations()
    {
        if (session()->get('role') !== 'super_admin') {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized.']);
            }
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $rawCooldown     = $this->request->getPost('vehicle_cooldown_minutes');
        $rawCooldownUnit = $this->request->getPost('vehicle_cooldown_unit') ?? 'minutes';
        $rawLogDays      = $this->request->getPost('log_retention_days');
        $rawDepDays      = $this->request->getPost('departure_retention_days');

        // Convert hours to minutes if unit is hours
        $cooldownVal = (int) $rawCooldown;
        if ($rawCooldownUnit === 'hours') {
            $cooldownVal = (int) round(((float) $rawCooldown) * 60);
        }

        if ($cooldownVal < 0 || $cooldownVal > 10080) { // Max 7 days
            $errMsg = 'Vehicle queue cooldown must be between 0 minutes and 7 days (10,080 minutes).';
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success'    => false,
                    'message'    => $errMsg,
                    'csrf_token' => csrf_token(),
                    'csrf_hash'  => csrf_hash(),
                ]);
            }
            return redirect()->back()->withInput()->with('error', $errMsg);
        }

        $logDaysVal = (int) $rawLogDays;
        if ($logDaysVal < 1 || $logDaysVal > 3650) {
            $errMsg = 'System logs retention must be between 1 and 3,650 days (10 years).';
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success'    => false,
                    'message'    => $errMsg,
                    'csrf_token' => csrf_token(),
                    'csrf_hash'  => csrf_hash(),
                ]);
            }
            return redirect()->back()->withInput()->with('error', $errMsg);
        }

        $depDaysVal = (int) $rawDepDays;
        if ($depDaysVal < 1 || $depDaysVal > 3650) {
            $errMsg = 'Departure history retention must be between 1 and 3,650 days (10 years).';
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success'    => false,
                    'message'    => $errMsg,
                    'csrf_token' => csrf_token(),
                    'csrf_hash'  => csrf_hash(),
                ]);
            }
            return redirect()->back()->withInput()->with('error', $errMsg);
        }

        $fields = [
            'vehicle_cooldown_minutes' => (string) $cooldownVal,
            'log_retention_days'       => (string) $logDaysVal,
            'departure_retention_days' => (string) $depDaysVal,
        ];

        $currentSettings = get_all_system_settings();
        $hasChange = false;
        foreach ($fields as $k => $v) {
            if ((string) ($currentSettings[$k] ?? '') !== $v) {
                $hasChange = true;
                break;
            }
        }

        if (! $hasChange) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success'    => true,
                    'no_change'  => true,
                    'message'    => 'No changes detected — operational settings are already up to date.',
                    'csrf_token' => csrf_token(),
                    'csrf_hash'  => csrf_hash(),
                ]);
            }
            return $this->noChangesResponse();
        }

        $model = new SystemSettingModel();
        $model->setMultiple($fields);
        get_all_system_settings(true);

        // Opportunistically run purges with new retention rules immediately
        try {
            (new \App\Models\LogModel())->purgeOldLogs($logDaysVal);
            (new \App\Models\QueueModel())->purgeOldDepartures($depDaysVal);
        } catch (\Throwable $e) {
            // Silently continue
        }

        // Notify queue sync across sockets if daemon active
        try {
            $this->broadcastUpdate('operational_settings_updated', [
                'vehicle_cooldown_minutes' => $cooldownVal,
                'log_retention_days'       => $logDaysVal,
                'departure_retention_days' => $depDaysVal,
            ]);
        } catch (\Throwable $e) {
            // Silently continue if daemon offline
        }

        $this->logActivity('System Settings', "Updated operational rules: Queue Cooldown={$cooldownVal}m, Log Retention={$logDaysVal}d, Departure Retention={$depDaysVal}d");

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success'    => true,
                'message'    => 'Operational & retention rules updated successfully.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
                'data'       => [
                    'vehicle_cooldown_minutes' => $cooldownVal,
                    'log_retention_days'       => $logDaysVal,
                    'departure_retention_days' => $depDaysVal,
                ],
            ]);
        }

        return redirect()->to('/admin/settings#operations')->with('success', 'Operational & retention rules updated successfully.');
    }

    /**
     * Display the Superadmin-only editor for public support and role help copy.
     */
    public function content()
    {
        if (session()->get('role') !== 'super_admin') {
            return redirect()->back()->with('error', 'You do not have access to this page.');
        }

        return view('admin/settings/content', [
            'title'    => 'Content Manager',
            'groups'   => \Config\ContentManagement::groups(),
            'settings' => get_all_system_settings(true),
        ]);
    }

    /**
     * Save plain-text content overrides. Blank fields restore built-in copy.
     */
    public function updateContent()
    {
        if (session()->get('role') !== 'super_admin') {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $definitions = \Config\ContentManagement::fields();
        $current      = get_all_system_settings();
        $updates      = [];
        $termsChanged = false;
        $totalLength  = 0;

        foreach ($definitions as $key => $definition) {
            $rawValue = $this->request->getPost($key);
            $value = is_string($rawValue)
                ? str_replace(["\r\n", "\r"], "\n", trim($rawValue))
                : '';
            $limit = $definition['type'] === 'textarea' ? 5000 : 180;
            if (mb_strlen($value) > $limit) {
                return redirect()->back()->withInput()->with(
                    'error',
                    $definition['label'] . " is too long. The maximum is {$limit} characters."
                );
            }

            $totalLength += mb_strlen($value);
            $storedValue = $value === '' ? null : $value;
            $oldValue    = isset($current[$key]) && trim((string) $current[$key]) !== ''
                ? trim((string) $current[$key])
                : null;

            if ($oldValue !== $storedValue) {
                $updates[$key] = $storedValue;
                if (str_starts_with($key, 'content_terms_')) {
                    $termsChanged = true;
                }
            }
        }

        if ($totalLength > 120000) {
            return redirect()->back()->withInput()->with('error', 'The combined content is too large. Please shorten the guide text.');
        }

        if ($updates === []) {
            return redirect()->to('/admin/settings/content')->with('info', 'No content changes were detected.');
        }

        if ($termsChanged) {
            $updates['content_terms_updated_at'] = date('Y-m-d');
        }

        (new SystemSettingModel())->setMultiple($updates);
        get_all_system_settings(true);

        $groups = [];
        foreach (array_keys($updates) as $key) {
            if (preg_match('/^content_([^_]+)/', $key, $matches)) {
                $groups[$matches[1]] = true;
            }
        }
        $this->logActivity('Content Manager', 'Updated managed content: ' . implode(', ', array_keys($groups)));

        return redirect()->to('/admin/settings/content')->with('success', 'Content updated successfully. Blank fields continue using the built-in copy.');
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

        $ext = $this->validatedImageExtension($file, true);
        if ($ext === null) {
            return $this->response->setStatusCode(400)->setJSON([
                'success'    => false,
                'message'    => 'Invalid file type. Only PNG, JPG, WEBP, and GIF are allowed.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'settings';
        if (! is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $newFilename = 'logo_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        if (! $file->move($uploadDir, $newFilename)) {
            return $this->response->setStatusCode(500)->setJSON([
                'success'    => false,
                'message'    => 'Failed to save uploaded logo.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        $this->deleteOldMedia('app_logo', $uploadDir);
        $relPath = 'uploads/settings/' . $newFilename;
        $model = new SystemSettingModel();
        $model->setSetting('app_logo', $relPath);
        get_all_system_settings(true);
        $this->broadcastBrandingChange('logo');

        $this->logActivity('System Settings', 'Uploaded new system logo: ' . $newFilename);

        return $this->response->setJSON([
            'success'    => true,
            'message'    => 'System logo updated successfully.',
            'image_url'  => base_url($relPath) . '?v=' . time(),
            'data'       => app_full_branding_payload('logo'),
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
        $this->broadcastBrandingChange('logo');

        $this->logActivity('System Settings', 'Reset system logo to default');

        return $this->response->setJSON([
            'success'    => true,
            'message'    => 'Logo reset to default seal.',
            'image_url'  => base_url('images/logo.webp'),
            'data'       => app_full_branding_payload('logo'),
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

        $ext = $this->validatedImageExtension($file);
        if ($ext === null) {
            return $this->response->setStatusCode(400)->setJSON([
                'success'    => false,
                'message'    => 'Invalid file type. Only PNG, JPG, and WEBP are allowed for backgrounds.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'settings';
        if (! is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

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
        $this->deleteOldMedia('app_background_image', $uploadDir);
        $model->setSetting('app_background_image', $relPath);

        $mode = $this->request->getPost('mode');
        if ($mode && in_array($mode, ['slideshow', 'single'], true)) {
            $model->setSetting('app_bg_mode', $mode);
        }

        get_all_system_settings(true);
        $this->broadcastBrandingChange('background');

        $this->logActivity('System Settings', 'Uploaded new system background picture: ' . $newFilename);

        return $this->response->setJSON([
            'success'              => true,
            'message'              => 'System background picture updated successfully.',
            'image_url'            => base_url($relPath) . '?v=' . time(),
            'app_bg_mode'          => $mode ?: get_setting('app_bg_mode', 'slideshow'),
            'app_background_image' => base_url($relPath) . '?v=' . time(),
            'app_bg_slideshow'     => array_values(app_bg_slideshow()),
            'data'                 => app_full_branding_payload('background'),
            'csrf_token'           => csrf_token(),
            'csrf_hash'            => csrf_hash(),
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
        $this->broadcastBrandingChange('background');

        $this->logActivity('System Settings', 'Reset system background to default');

        return $this->response->setJSON([
            'success'              => true,
            'message'              => 'Background picture reset to default.',
            'image_url'            => base_url('images/logo.webp'),
            'app_bg_mode'          => get_setting('app_bg_mode', 'slideshow'),
            'app_background_image' => base_url('images/logo.webp'),
            'app_bg_slideshow'     => array_values(app_bg_slideshow()),
            'data'                 => app_full_branding_payload('background'),
            'csrf_token'           => csrf_token(),
            'csrf_hash'            => csrf_hash(),
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
        $this->broadcastBrandingChange('background');

        $this->logActivity('System Settings', 'Switched background display mode to: ' . $mode);

        return $this->response->setJSON([
            'success'              => true,
            'mode'                 => $mode,
            'app_bg_mode'          => $mode,
            'app_background_image' => app_bg_image(),
            'app_bg_slideshow'     => array_values(app_bg_slideshow()),
            'message'              => 'Background mode switched to ' . ($mode === 'slideshow' ? 'Dynamic Rotating Slideshow' : 'Single Hero Background') . '.',
            'data'                 => app_full_branding_payload('background'),
            'csrf_token'           => csrf_token(),
            'csrf_hash'            => csrf_hash(),
        ]);
    }

    /**
     * Save background display mode via explicit Save button.
     */
    public function saveBgMode()
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
        $this->broadcastBrandingChange('background');

        $this->logActivity('System Settings', 'Saved background display mode to: ' . $mode);

        return $this->response->setJSON([
            'success'              => true,
            'mode'                 => $mode,
            'app_bg_mode'          => $mode,
            'app_background_image' => app_bg_image(),
            'app_bg_slideshow'     => array_values(app_bg_slideshow()),
            'message'              => 'Background display mode (' . ($mode === 'slideshow' ? 'Dynamic Rotating Slideshow' : 'Single Hero Background') . ') saved successfully.',
            'data'                 => app_full_branding_payload('background'),
            'csrf_token'           => csrf_token(),
            'csrf_hash'            => csrf_hash(),
        ]);
    }

    /**
     * Upload custom login page hero illustration.
     */
    public function uploadLoginCard()
    {
        if (session()->get('role') !== 'super_admin') {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized.']);
        }

        $file = $this->request->getFile('login_card');
        if (! $file || ! $file->isValid()) {
            return $this->response->setStatusCode(400)->setJSON([
                'success'    => false,
                'message'    => 'No valid image file was uploaded.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        $ext = $this->validatedImageExtension($file);
        if ($ext === null) {
            return $this->response->setStatusCode(400)->setJSON([
                'success'    => false,
                'message'    => 'Only PNG, JPG, and WEBP image formats are supported.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        if ($file->getSizeByUnit('mb') > 8) {
            return $this->response->setStatusCode(400)->setJSON([
                'success'    => false,
                'message'    => 'Login hero image file size must be less than 8 MB.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'settings';
        if (! is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $newFilename = 'login_card_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        try {
            $file->move($uploadDir, $newFilename);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success'    => false,
                'message'    => 'Failed to save uploaded file: ' . $e->getMessage(),
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        $this->deleteOldMedia('app_login_card_image', $uploadDir);
        $relPath = 'uploads/settings/' . $newFilename;
        $model = new SystemSettingModel();
        $model->setSetting('app_login_card_image', $relPath);
        get_all_system_settings(true);
        $this->broadcastBrandingChange('identity');

        $this->logActivity('System Settings', 'Uploaded new login page hero illustration: ' . $newFilename);

        return $this->response->setJSON([
            'success'    => true,
            'message'    => 'Login page hero illustration updated successfully.',
            'image_url'  => base_url($relPath) . '?v=' . time(),
            'filename'   => $newFilename,
            'data'       => app_full_branding_payload('identity'),
            'csrf_token' => csrf_token(),
            'csrf_hash'  => csrf_hash(),
        ]);
    }

    /**
     * Reset login page hero illustration to default 3-vehicle artwork.
     */
    public function resetLoginCard()
    {
        if (session()->get('role') !== 'super_admin') {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized.']);
        }

        $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'settings';
        $this->deleteOldMedia('app_login_card_image', $uploadDir);

        $model = new SystemSettingModel();
        $model->setSetting('app_login_card_image', null);
        get_all_system_settings(true);
        $this->broadcastBrandingChange('identity');

        $this->logActivity('System Settings', 'Reset login page hero illustration to default artwork');

        return $this->response->setJSON([
            'success'    => true,
            'message'    => 'Login hero illustration restored to default artwork.',
            'image_url'  => base_url('images/logo.webp'),
            'data'       => app_full_branding_payload('identity'),
            'csrf_token' => csrf_token(),
            'csrf_hash'  => csrf_hash(),
        ]);
    }

    /**
     * Upload a custom picture for any slideshow slot (Slot 1 to 5+).
     */
    public function uploadSlideshowSlot()
    {
        if (session()->get('role') !== 'super_admin') {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized.']);
        }

        $slot = (int) $this->request->getPost('slot');
        if ($slot < 1) {
            return $this->response->setStatusCode(400)->setJSON([
                'success'    => false,
                'message'    => 'Invalid slideshow slot.',
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

        $ext = $this->validatedImageExtension($file);
        if ($ext === null) {
            return $this->response->setStatusCode(400)->setJSON([
                'success'    => false,
                'message'    => 'Invalid file type. Only PNG, JPG, and WEBP are allowed.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'settings';
        if (! is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $settingKey = "app_bg_slideshow_{$slot}";
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
        $this->deleteOldMedia($settingKey, $uploadDir);
        $model->setSetting($settingKey, $relPath);
        get_all_system_settings(true);
        $this->broadcastBrandingChange('background');

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
     * Dynamically add a new slideshow photo slot (Slot 6+).
     */
    public function addSlideshowSlot()
    {
        if (session()->get('role') !== 'super_admin') {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized.']);
        }

        $file = $this->request->getFile('image');
        if (! $file || ! $file->isValid()) {
            return $this->response->setStatusCode(400)->setJSON([
                'success'    => false,
                'message'    => 'Please select a valid image file to add.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        if ($file->getSize() > 8 * 1024 * 1024) {
            return $this->response->setStatusCode(400)->setJSON([
                'success'    => false,
                'message'    => 'The image exceeds the 8 MB maximum allowed size.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        $ext = $this->validatedImageExtension($file);
        if ($ext === null) {
            return $this->response->setStatusCode(400)->setJSON([
                'success'    => false,
                'message'    => 'Invalid file type. Only PNG, JPG, and WEBP are allowed.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        // Find next slot index
        $allSettings = get_all_system_settings(true);
        $highestSlot = 5;
        foreach ($allSettings as $k => $val) {
            if (preg_match('/^app_bg_slideshow_([0-9]+)$/', $k, $matches)) {
                $num = (int) $matches[1];
                if ($num > $highestSlot) {
                    $highestSlot = $num;
                }
            }
        }
        $nextSlot = $highestSlot + 1;

        $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'settings';
        if (! is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }

        $newFilename = "bg_slot_{$nextSlot}_" . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;

        if (! $file->move($uploadDir, $newFilename)) {
            return $this->response->setStatusCode(500)->setJSON([
                'success'    => false,
                'message'    => 'Failed to save new slideshow photo.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        $relPath = 'uploads/settings/' . $newFilename;
        $settingKey = "app_bg_slideshow_{$nextSlot}";

        $model = new SystemSettingModel();
        $model->setSetting($settingKey, $relPath);

        // Also add $nextSlot to active slots list
        $slotsSetting = get_system_setting('app_bg_slideshow_slots');
        if (! empty($slotsSetting)) {
            $rawSlots = explode(',', $slotsSetting);
            $activeSlots = [];
            foreach ($rawSlots as $rs) {
                $v = (int) trim($rs);
                if ($v > 0) $activeSlots[] = $v;
            }
            $activeSlots[] = $nextSlot;
            $activeSlots = array_values(array_unique($activeSlots));
        } else {
            $activeSlots = [1, 2, 3, 4, 5, $nextSlot];
            foreach ($allSettings as $k => $val) {
                if (preg_match('/^app_bg_slideshow_([6-9]|[1-9][0-9]+)$/', $k, $matches) && ! empty($val)) {
                    $activeSlots[] = (int) $matches[1];
                }
            }
            $activeSlots = array_values(array_unique($activeSlots));
        }
        sort($activeSlots, SORT_NUMERIC);
        $model->setSetting('app_bg_slideshow_slots', implode(',', $activeSlots));

        get_all_system_settings(true);
        $this->broadcastBrandingChange('background');

        $this->logActivity('System Settings', "Added new slideshow slot {$nextSlot}: {$newFilename}");

        return $this->response->setJSON([
            'success'    => true,
            'slot'       => $nextSlot,
            'message'    => "Slideshow Slot {$nextSlot} added successfully.",
            'image_url'  => base_url($relPath) . '?v=' . time(),
            'filename'   => $newFilename,
            'title'      => "Custom Photo Slot {$nextSlot}",
            'csrf_token' => csrf_token(),
            'csrf_hash'  => csrf_hash(),
        ]);
    }

    /**
     * Delete any slideshow slot (1..N) and update the active slots list.
     */
    public function deleteSlideshowSlot()
    {
        if (session()->get('role') !== 'super_admin') {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized.']);
        }

        $slot = (int) $this->request->getPost('slot');
        if ($slot < 1) {
            return $this->response->setStatusCode(400)->setJSON([
                'success'    => false,
                'message'    => 'Invalid slideshow slot.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        // Determine currently active slots
        $slotsSetting = get_system_setting('app_bg_slideshow_slots');
        if (! empty($slotsSetting)) {
            $rawSlots = explode(',', $slotsSetting);
            $activeSlots = [];
            foreach ($rawSlots as $rs) {
                $v = (int) trim($rs);
                if ($v > 0) {
                    $activeSlots[] = $v;
                }
            }
            $activeSlots = array_values(array_unique($activeSlots));
        } else {
            $activeSlots = [1, 2, 3, 4, 5];
            $allSettings = get_all_system_settings(true);
            foreach ($allSettings as $k => $val) {
                if (preg_match('/^app_bg_slideshow_([6-9]|[1-9][0-9]+)$/', $k, $matches) && ! empty($val)) {
                    $activeSlots[] = (int) $matches[1];
                }
            }
            $activeSlots = array_values(array_unique($activeSlots));
        }

        if (count($activeSlots) <= 1) {
            return $this->response->setStatusCode(400)->setJSON([
                'success'    => false,
                'message'    => 'Cannot delete the last remaining slideshow picture. At least 1 picture is required.',
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        // Remove the slot from active slots
        $activeSlots = array_values(array_diff($activeSlots, [$slot]));
        sort($activeSlots, SORT_NUMERIC);

        $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'settings';
        $settingKey = "app_bg_slideshow_{$slot}";
        $this->deleteOldMedia($settingKey, $uploadDir);

        $model = new SystemSettingModel();
        // Update active slots list
        $model->setSetting('app_bg_slideshow_slots', implode(',', $activeSlots));

        // If custom uploaded picture was saved in system_settings, remove or reset it
        $db = \Config\Database::connect();
        if ($db->tableExists('system_settings')) {
            $db->table('system_settings')->where('setting_key', $settingKey)->delete();
        }

        get_all_system_settings(true);
        $this->broadcastBrandingChange('background');

        $remainingCount = count($activeSlots);
        $this->logActivity('System Settings', "Deleted slideshow slot {$slot}. Active slots remaining: " . implode(',', $activeSlots));

        return $this->response->setJSON([
            'success'      => true,
            'slot'         => $slot,
            'active_slots' => $activeSlots,
            'count'        => $remainingCount,
            'message'      => "Slideshow picture removed successfully. ({$remainingCount} active)",
            'csrf_token'   => csrf_token(),
            'csrf_hash'    => csrf_hash(),
        ]);
    }

    /**
     * Reset a single slideshow slot back to default artwork, or delete if slot > 5.
     */
    public function resetSlideshowSlot()
    {
        if (session()->get('role') !== 'super_admin') {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized.']);
        }

        $slot = (int) $this->request->getPost('slot');
        if ($slot < 1) {
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
        if ($slot > 5) {
            $db = \Config\Database::connect();
            if ($db->tableExists('system_settings')) {
                $db->table('system_settings')->where('setting_key', $settingKey)->delete();
            }
            get_all_system_settings(true);
            $this->broadcastBrandingChange('background');

            return $this->response->setJSON([
                'success'    => true,
                'deleted'    => true,
                'slot'       => $slot,
                'message'    => "Custom slot {$slot} removed.",
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ]);
        }

        $model->setSetting($settingKey, null);
        get_all_system_settings(true);
        $this->broadcastBrandingChange('background');

        $defaultFiles = [
            1 => 'images/bg/bg1_townhall.webp',
            2 => 'images/bg/bg2_aerial_port.webp',
            3 => 'images/bg/bg3_aerial_town.webp',
            4 => 'images/bg/bg4_terminal_exterior.webp',
            5 => 'images/bg/bg5_terminal_bay.webp',
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
     * Reset all slideshow slots back to system default artworks (resets 1-5, cleans up 6+, resets active slots list).
     */
    public function resetAllSlideshow()
    {
        if (session()->get('role') !== 'super_admin') {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Unauthorized.']);
        }

        $uploadDir = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'settings';
        $model = new SystemSettingModel();
        $allSettings = get_all_system_settings(true);

        $defaultFiles = [
            1 => 'images/bg/bg1_townhall.webp',
            2 => 'images/bg/bg2_aerial_port.webp',
            3 => 'images/bg/bg3_aerial_town.webp',
            4 => 'images/bg/bg4_terminal_exterior.webp',
            5 => 'images/bg/bg5_terminal_bay.webp',
        ];

        for ($i = 1; $i <= 5; $i++) {
            $this->deleteOldMedia("app_bg_slideshow_{$i}", $uploadDir);
            $model->setSetting("app_bg_slideshow_{$i}", null);
        }

        // Clean up any extra slots > 5
        $db = \Config\Database::connect();
        foreach ($allSettings as $k => $val) {
            if (preg_match('/^app_bg_slideshow_([6-9]|[1-9][0-9]+)$/', $k, $matches)) {
                $this->deleteOldMedia($k, $uploadDir);
                if ($db->tableExists('system_settings')) {
                    $db->table('system_settings')->where('setting_key', $k)->delete();
                }
            }
        }

        // Reset active slots list back to base 1,2,3,4,5
        $model->setSetting('app_bg_slideshow_slots', '1,2,3,4,5');

        get_all_system_settings(true);
        $this->broadcastBrandingChange('background');

        $this->logActivity('System Settings', 'Reset all slideshow background pictures to defaults');

        return $this->response->setJSON([
            'success'    => true,
            'message'    => 'All slideshow pictures have been restored to system defaults.',
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

    /**
     * Broadcast branding changes across WebSocket to all connected clients in real time.
     */
    protected function broadcastBrandingChange(?string $category = null): void
    {
        try {
            $payload = app_full_branding_payload($category);
            $this->broadcastUpdate('branding_updated', $payload);
        } catch (\Throwable $e) {
            // Silently continue if daemon offline
        }
    }
}

