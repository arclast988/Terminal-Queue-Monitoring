<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

final class AdminMobileNavigationTest extends CIUnitTestCase
{
    public function testVehicleHeaderUsesCompactActionGroup(): void
    {
        $view = file_get_contents(APPPATH . 'Views/admin/vehicles/index.php');
        $css = file_get_contents(FCPATH . 'assets/css/responsive.css');

        $this->assertStringContainsString('page-header-actions vehicle-header-actions', $view);
        $this->assertStringContainsString('.page-header-modern .vehicle-header-actions', $css);
        $this->assertStringContainsString('flex-wrap: nowrap !important;', $css);
        $this->assertStringContainsString('flex: 1 1 0 !important;', $css);
        $this->assertStringContainsString('min-height: 40px;', $css);
        $this->assertStringContainsString('white-space: nowrap;', $css);
        $this->assertStringNotContainsString(".page-header-modern .btn-modern {\n        width: 100%;", $css);
    }

    public function testMobileDrawerAvoidsFullScreenBlurAndUsesTouchScrolling(): void
    {
        $css = file_get_contents(FCPATH . 'assets/css/navigation.css');

        $overlayStart = strpos($css, '.sidebar-drawer-overlay {');
        $overlayEnd = strpos($css, '}', $overlayStart);
        $overlayRules = substr($css, $overlayStart, $overlayEnd - $overlayStart);

        $this->assertStringContainsString('backdrop-filter: none;', $overlayRules);
        $this->assertStringContainsString('-webkit-backdrop-filter: none;', $overlayRules);
        $this->assertStringContainsString('transform: translate3d(0, 0, 0);', $css);
        $this->assertStringContainsString('touch-action: pan-y;', $css);
        $this->assertStringContainsString('width: min(288px, 86vw);', $css);
    }

    public function testUpdatedMobileStylesAreCacheBusted(): void
    {
        $header = file_get_contents(APPPATH . 'Views/templates/header.php');
        $navbar = file_get_contents(APPPATH . 'Views/templates/navbar.php');

        $this->assertStringContainsString('responsive.css?v=20260920_1', $header);
        $this->assertStringContainsString('navigation.css?v=20260920_1', $navbar);
    }

    public function testRequestedMobileActionGroupsKeepTheirAlignment(): void
    {
        $passwordView = file_get_contents(APPPATH . 'Views/auth/change_password.php');
        $announcementView = file_get_contents(APPPATH . 'Views/admin/announcements/index.php');

        $this->assertStringContainsString('change-password-actions d-flex', $passwordView);
        $this->assertStringContainsString('.change-password-actions > .btn-modern', $passwordView);
        $this->assertStringContainsString('flex-direction: row !important;', $passwordView);

        $this->assertStringContainsString('.announcement-actions-wrap', $announcementView);
        $this->assertStringContainsString('margin-left: auto !important;', $announcementView);
        $this->assertStringContainsString('width: auto !important;', $announcementView);
    }
}
