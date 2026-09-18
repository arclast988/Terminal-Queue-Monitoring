<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class SystemSettingsTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('app');
    }

    public function testDefaultBrandingHelpersReturnExpectedValues(): void
    {
        $appName = app_name();
        $this->assertIsString($appName);
        $this->assertNotEmpty($appName);

        $subtitle = app_subtitle();
        $this->assertIsString($subtitle);

        $systemTitle = app_system_title();
        $this->assertIsString($systemTitle);
        $this->assertNotEmpty($systemTitle);

        $acronym = app_acronym();
        $this->assertIsString($acronym);
        $this->assertNotEmpty($acronym);

        $logo = app_logo();
        $this->assertIsString($logo);
        $this->assertNotEmpty($logo);

        $credit = app_footer_credit();
        $this->assertIsString($credit);
    }

    public function testThemeCssHelperGeneratesStyleBlock(): void
    {
        $themeCss = app_theme_css();
        $this->assertIsString($themeCss);
        $this->assertStringContainsString('<style id="app-dynamic-themes">', $themeCss);
        $this->assertStringContainsString('guest-theme', $themeCss);
        $this->assertStringContainsString('admin-theme', $themeCss);
        $this->assertStringContainsString('staff-theme', $themeCss);
        $this->assertStringContainsString('</style>', $themeCss);
    }

    public function testSuperAdminNavIncludesBrandingLink(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'role'       => 'super_admin',
            'full_name'  => 'System Administrator',
            'username'   => 'superadmin',
        ]);

        $html = view('partials/nav-admin');

        $this->assertStringContainsString('admin/settings', $html);
        $this->assertStringContainsString('System Themes', $html);
    }

    public function testRegularAdminNavExcludesBrandingLink(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'role'       => 'admin',
            'full_name'  => 'Regular Admin',
            'username'   => 'admin',
        ]);

        $html = view('partials/nav-admin');

        $this->assertStringNotContainsString('admin/settings', $html);
        $this->assertStringNotContainsString('System Themes', $html);
    }

    public function testSuperAdminHeaderDrawerIncludesBrandingLink(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'role'       => 'super_admin',
            'full_name'  => 'System Administrator',
            'username'   => 'superadmin',
        ]);

        $html = view('partials/header');

        $this->assertStringContainsString('admin/settings', $html);
        $this->assertStringContainsString('System Themes', $html);
    }

    public function testRegularAdminHeaderDrawerExcludesBrandingLink(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'role'       => 'admin',
            'full_name'  => 'Regular Admin',
            'username'   => 'admin',
        ]);

        $html = view('partials/header');

        $this->assertStringNotContainsString('admin/settings', $html);
        $this->assertStringNotContainsString('System Themes', $html);
    }

    public function testSettingsViewRendersTabsAndForms(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'role'       => 'super_admin',
            'full_name'  => 'System Administrator',
            'username'   => 'superadmin',
        ]);

        $html = view('admin/settings/index', [
            'title'    => 'System Branding & Settings',
            'settings' => [
                'app_name'     => 'Palompon Transit',
                'system_title' => 'Palompon Transit Terminal Management System',
                'acronym'      => 'PTTM',
            ],
        ]);

        // Verify tabs
        $this->assertStringContainsString('id="tab-identity"', $html);
        $this->assertStringContainsString('id="tab-media"', $html);
        $this->assertStringContainsString('id="tab-themes"', $html);
        $this->assertStringContainsString('id="tab-footer"', $html);

        // Verify form inputs
        $this->assertStringContainsString('name="app_name"', $html);
        $this->assertStringContainsString('name="system_title"', $html);
        $this->assertStringContainsString('name="acronym"', $html);
        $this->assertStringContainsString('id="logoFileInput"', $html);
        $this->assertStringContainsString('id="bgFileInput"', $html);
        $this->assertStringContainsString('name="theme_guest_primary"', $html);
        $this->assertStringContainsString('name="theme_admin_primary"', $html);
        $this->assertStringContainsString('name="theme_staff_primary"', $html);
        $this->assertStringContainsString('name="footer_about_title"', $html);
        $this->assertStringContainsString('name="footer_credit"', $html);
    }

    public function testLoginViewUsesAppDynamicNaming(): void
    {
        $loginHtml = view('auth/login');
        $this->assertStringContainsString(esc(app_name()), $loginHtml);
        $this->assertStringContainsString('app-dynamic-themes', $loginHtml);
    }

    public function testForgotPasswordViewUsesAppDynamicNaming(): void
    {
        $forgotHtml = view('auth/forgot_password');
        $this->assertStringContainsString(esc(app_name()), $forgotHtml);
        $this->assertStringContainsString('app-dynamic-themes', $forgotHtml);
    }

    public function testVerifyCodeViewUsesAppDynamicNaming(): void
    {
        $verifyHtml = view('auth/verify_code', ['token' => 'test_token_123']);
        $this->assertStringContainsString(esc(app_name()), $verifyHtml);
        $this->assertStringContainsString('app-dynamic-themes', $verifyHtml);
    }

    public function testResetPasswordViewUsesAppDynamicNaming(): void
    {
        $resetHtml = view('auth/reset_password', ['token' => 'test_token_123']);
        $this->assertStringContainsString(esc(app_name()), $resetHtml);
        $this->assertStringContainsString('app-dynamic-themes', $resetHtml);
    }

    public function testFooterAndGuestFooterUseDynamicBranding(): void
    {
        $footerHtml = view('partials/footer');
        $this->assertStringContainsString(esc(app_name()), $footerHtml);

        $guestFooterHtml = view('templates/guestfooter');
        $this->assertStringContainsString(esc(app_system_title()), $guestFooterHtml);
        $this->assertStringContainsString(esc(app_acronym()), $guestFooterHtml);
    }

    public function testSuperAdminProfileDropdownContainsBrandingLink(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'role'       => 'super_admin',
            'full_name'  => 'System Administrator',
            'username'   => 'superadmin',
        ]);

        $profileHtml = view('partials/nav-profile');
        $this->assertStringContainsString('admin/settings', $profileHtml);
        $this->assertStringContainsString('System Themes', $profileHtml);

        // Test regular admin does not see branding in profile dropdown
        $session->set(['role' => 'admin']);
        $adminProfileHtml = view('partials/nav-profile');
        $this->assertStringNotContainsString('admin/settings', $adminProfileHtml);
        $this->assertStringNotContainsString('System Themes', $adminProfileHtml);
    }

    public function testVehicleTypeModelContainsPhotoField(): void
    {
        $vtModel = new \App\Models\VehicleTypeModel();
        $this->assertContains('photo', $vtModel->allowedFields);
    }

    public function testVehicleTypePhotoHelper(): void
    {
        $this->assertNull(vehicle_type_photo(null));
        $this->assertNull(vehicle_type_photo(''));
    }

    public function testPrintReportsContainBrandGroupAndLogo(): void
    {
        $historyHtml = view('admin/history/print_history', [
            'title'       => 'Print Departures',
            'results'     => [],
            'from_date'   => '',
            'to_date'     => '',
            'vehicle_type'=> '',
            'destination' => '',
        ]);
        $this->assertStringContainsString('terminal-brand-group', $historyHtml);
        $this->assertStringContainsString('report-logo', $historyHtml);

        $logsHtml = view('admin/logs/print_logs', [
            'title'        => 'Print Logs',
            'results'      => [],
            'from_date'    => '',
            'to_date'      => '',
            'action_type'  => '',
            'selected_user'=> null,
            'user_id'      => '',
            'search'       => '',
            'generated_at' => 'now',
        ]);
        $this->assertStringContainsString('terminal-brand-group', $logsHtml);
        $this->assertStringContainsString('report-logo', $logsHtml);
    }

    public function testOperationalSettingsHelpersReturnDefaults(): void
    {
        $this->assertSame(30, vehicle_cooldown_minutes());
        $this->assertSame(60, log_retention_days());
        $this->assertSame(60, departure_retention_days());
    }

    public function testOperationalSettingsCustomValuesPersistAndResolve(): void
    {
        $db = \Config\Database::connect();
        if ($db && !$db->tableExists('system_settings')) {
            $runner = service('migrations');
            $runner->setNamespace('App');
            $runner->latest('tests');
        }

        $model = new \App\Models\SystemSettingModel();
        $model->setMultiple([
            'vehicle_cooldown_minutes' => '45',
            'log_retention_days'       => '90',
            'departure_retention_days' => '120',
        ]);
        get_all_system_settings(true);

        $this->assertSame(45, vehicle_cooldown_minutes());
        $this->assertSame(90, log_retention_days());
        $this->assertSame(120, departure_retention_days());

        // Restore defaults
        $model->setMultiple([
            'vehicle_cooldown_minutes' => '30',
            'log_retention_days'       => '60',
            'departure_retention_days' => '60',
        ]);
        get_all_system_settings(true);

        $this->assertSame(30, vehicle_cooldown_minutes());
        $this->assertSame(60, log_retention_days());
        $this->assertSame(60, departure_retention_days());
    }

    public function testAdminSettingsViewContainsOperationsTabAndInputs(): void
    {
        $html = view('admin/settings/index', [
            'settings' => get_all_system_settings(),
        ]);
        $this->assertStringContainsString('tabBtn-operations', $html);
        $this->assertStringContainsString('tab-operations', $html);
        $this->assertStringContainsString('vehicle_cooldown_minutes', $html);
        $this->assertStringContainsString('log_retention_days', $html);
        $this->assertStringContainsString('departure_retention_days', $html);
        $this->assertStringContainsString('admin/settings/update-operations', $html);
    }

    public function testLoginCardHelpersAndDefaults(): void
    {
        $defaultImg = app_login_card_image();
        $this->assertIsString($defaultImg);
        $this->assertStringContainsString('logo.webp', rawurldecode($defaultImg));
        $this->assertFalse(app_has_custom_login_card());

        $testFile = FCPATH . 'uploads/settings/custom_card.png';
        if (!is_dir(dirname($testFile))) {
            @mkdir(dirname($testFile), 0755, true);
        }
        file_put_contents($testFile, 'dummy content');

        $model = new \App\Models\SystemSettingModel();
        $model->setSetting('app_login_card_image', 'uploads/settings/custom_card.png');
        get_all_system_settings(true);

        $this->assertTrue(app_has_custom_login_card());
        $this->assertStringContainsString('custom_card.png', app_login_card_image());

        // Restore default & clean up test file
        $model->setSetting('app_login_card_image', null);
        get_all_system_settings(true);
        if (file_exists($testFile)) {
            @unlink($testFile);
        }

        $this->assertFalse(app_has_custom_login_card());
        $this->assertStringContainsString('logo.webp', rawurldecode(app_login_card_image()));
    }

    public function testFullBrandingPayloadContainsAllKeys(): void
    {
        $payload = app_full_branding_payload();
        $this->assertIsArray($payload);

        $expectedKeys = [
            'app_name',
            'app_subtitle',
            'system_title',
            'acronym',
            'app_logo',
            'app_background_image',
            'app_bg_mode',
            'app_bg_slideshow',
            'app_login_card_image',
            'theme_guest_primary',
            'theme_guest_nav_bg',
            'theme_guest_nav_text',
            'theme_staff_primary',
            'theme_staff_nav_bg',
            'theme_staff_nav_text',
            'theme_admin_primary',
            'theme_admin_nav_bg',
            'theme_admin_nav_text',
            'footer_about_title',
            'footer_about_desc',
            'footer_credit',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $payload, "Payload missing key: {$key}");
        }
    }

    public function testAdminSettingsViewContainsSaveBgButtonAndLoginCardManager(): void
    {
        $html = view('admin/settings/index', [
            'settings' => get_all_system_settings(),
        ]);

        $this->assertStringContainsString('btnSaveLogo', $html);
        $this->assertStringContainsString('Save Logo', $html);
        $this->assertStringContainsString('btnSaveBgSettings', $html);
        $this->assertStringContainsString('Save Background & Display Mode', $html);
        $this->assertStringContainsString('loginCardUploadZone', $html);
        $this->assertStringContainsString('loginCardActiveImg', $html);
        $this->assertStringContainsString('btnSaveLoginCard', $html);
        $this->assertStringContainsString('Save Login Hero Illustration', $html);
        $this->assertStringContainsString('btnResetLoginCard', $html);
        $this->assertStringContainsString('admin/settings/save-bg-mode', $html);
        $this->assertStringContainsString('admin/settings/upload-login-card', $html);
        $this->assertStringContainsString('admin/settings/reset-login-card', $html);
    }

    public function testLoginViewRendersDynamicLoginCardImage(): void
    {
        $html = view('auth/login');

        $this->assertStringContainsString('id="loginHeroImg"', $html);
        $this->assertStringContainsString(app_login_card_image(), $html);
    }

    public function testGuestEnhancedDashboardContainsDepartureRulesModalWithCorrectAttributes(): void
    {
        $html = view('public/enhanced_dashboard', [
            'departure_rules' => [],
            'active_queue'    => [],
            'routes'          => [],
            'announcements'   => [],
        ]);

        $this->assertStringContainsString('id="routeAverageCard"', $html);
        $this->assertStringContainsString('onclick="openRouteAverageModal()"', $html);
        $this->assertStringContainsString('id="routeAverageModal"', $html);
        $this->assertStringContainsString('route-average-modal modal', $html);
        $this->assertStringContainsString('id="routeAverageClose"', $html);
        $this->assertStringContainsString('onclick="closeRouteAverageModal()"', $html);
        $this->assertStringContainsString('window.openRouteAverageModal', $html);
        $this->assertStringContainsString('window.closeRouteAverageModal', $html);
        $this->assertStringContainsString('z-index: 100005 !important;', $html);
    }
}


