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
        $this->assertStringContainsString('@media (min-width: 1281px)', $css);
        $this->assertStringContainsString('min-height: 70px;', $css);
        $this->assertStringContainsString('min-height: 66px;', $css);
    }

    public function testUpdatedMobileStylesAreCacheBusted(): void
    {
        $header = file_get_contents(APPPATH . 'Views/templates/header.php');
        $navbar = file_get_contents(APPPATH . 'Views/templates/navbar.php');

        $this->assertStringContainsString('responsive.css?v=20260920_1', $header);
        $this->assertStringContainsString('navigation.css?v=20260921_15', $navbar);
    }

    public function testLoggedInHeaderProvidesAResponsivePhilippineClock(): void
    {
        $header = file_get_contents(APPPATH . 'Views/partials/header.php');
        $profile = file_get_contents(APPPATH . 'Views/partials/nav-profile.php');
        $template = file_get_contents(APPPATH . 'Views/templates/header.php');
        $css = file_get_contents(FCPATH . 'assets/css/navigation.css');

        $this->assertNotFalse($header);
        $this->assertNotFalse($css);
        $this->assertStringContainsString("\$isAdmin || \$role === 'staff'", $header);
        $this->assertStringContainsString('id="operationsHeaderClock"', $header);
        $this->assertStringContainsString('id="profileOperationsHeaderClock"', $profile);
        $this->assertStringContainsString("document.querySelectorAll('.operations-header-clock')", $header);
        $this->assertStringContainsString("timeZone: 'Asia/Manila'", $header);
        $this->assertStringContainsString("hourCycle: 'h23'", $header);
        $this->assertStringContainsString("date('H:i')", $header);
        $this->assertStringNotContainsString('operations-header-timezone', $header);
        $this->assertStringNotContainsString('compactClockMedia', $header);
        $this->assertLessThan(strpos($header, '</nav>'), strpos($header, 'class="operations-header-time"'));
        $this->assertStringContainsString('document.hidden', $header);
        $this->assertStringContainsString('.operations-header-time {', $css);
        $this->assertStringContainsString('font-variant-numeric: tabular-nums;', $css);
        $this->assertStringContainsString('--site-header-height: 74px;', $css);
        $this->assertStringContainsString('--site-header-height: 70px;', $css);
        $this->assertStringContainsString('height: 74px !important;', $css);
        $this->assertStringContainsString('height: 70px !important;', $css);
        $this->assertStringContainsString('padding: 6px 10px 20px !important;', $css);
        $this->assertStringContainsString('padding: 4px 6px 20px !important;', $css);
        $this->assertStringContainsString('transform: translateY(7px);', $css);
        $this->assertStringContainsString('transform: translateY(5px);', $css);
        $this->assertStringContainsString('width: 38px !important;', $css);
        $this->assertStringContainsString('font-size: 17px !important;', $css);
        $this->assertStringContainsString('width: 48px !important;', $css);
        $this->assertStringContainsString('font-size: clamp(13px, 3.6vw, 15.5px) !important;', $css);
        $this->assertStringContainsString('font-size: 10px !important;', $css);
        $this->assertStringContainsString(".drawer-logo {\n    width: 38px;\n    height: 38px;", str_replace("\r\n", "\n", $css));
        $this->assertStringContainsString(".drawer-brand-title {\n    font-size: 14px;", str_replace("\r\n", "\n", $css));
        $this->assertStringContainsString(".drawer-brand-subtitle {\n    font-size: 9.5px;", str_replace("\r\n", "\n", $css));
        $this->assertStringContainsString('max-width: 100% !important;', $css);
        $this->assertStringContainsString("#site-header .header-left-cluster {\n    flex: 1 1 auto;", str_replace("\r\n", "\n", $css));
        $this->assertStringContainsString('.header-left-cluster > .operations-header-time {', $css);
        $this->assertStringContainsString('grid-template-rows: 13px auto;', $css);
        $this->assertStringContainsString('justify-items: start;', $css);
        $this->assertStringContainsString('.profile-operations-time {', $css);
        $this->assertStringContainsString('margin-left: 0;', $css);
        $this->assertStringContainsString('@media (max-width: 1280px)', $css);
        $this->assertStringContainsString('padding: 6px 16px 20px !important;', $css);
        $this->assertStringContainsString('position: absolute;', $css);
        $this->assertStringContainsString('transform: translateY(-50%);', $css);
        $this->assertStringContainsString('width: 42px;', $css);
        $this->assertStringContainsString('justify-content: center;', $css);
        $this->assertStringContainsString('background: transparent;', $css);
        $this->assertStringContainsString('top: calc(var(--site-header-height, 74px) + 4px) !important;', $css);
        $this->assertStringContainsString('body.guest-theme.layout-lock header#site-header', $css);
        $this->assertStringContainsString('@media (max-width: 360px)', $css);
        $this->assertStringContainsString('--site-header-height: 60px;', $css);
        $this->assertStringContainsString('max-height: 74px !important;', $template);
        $this->assertStringContainsString('max-height: 70px !important;', $template);
        $this->assertStringContainsString('var maxCap = isMobile ? 82 : 84;', $template);
        $this->assertStringContainsString('calc(var(--site-header-height, 60px) + 12px)', $template);
        $this->assertStringContainsString('calc(var(--site-header-height, 56px) + 12px)', $template);
        $this->assertStringNotContainsString('max-height: 60px !important;', $template);
    }

    public function testClosingDrawerMovesFocusBeforeApplyingAriaHidden(): void
    {
        $header = file_get_contents(APPPATH . 'Views/partials/header.php');

        $this->assertStringContainsString('aria-hidden="true" inert', $header);
        $this->assertStringContainsString("drawer.removeAttribute('inert');", $header);
        $this->assertStringContainsString('if (drawer.contains(document.activeElement))', $header);
        $this->assertStringContainsString('hamburgerBtn.focus({ preventScroll: true });', $header);
        $this->assertStringContainsString("drawer.setAttribute('aria-hidden', 'true');", $header);
        $this->assertStringContainsString("drawer.setAttribute('inert', '');", $header);

        $focusPosition = strpos($header, 'hamburgerBtn.focus({ preventScroll: true });');
        $hiddenPosition = strpos($header, "drawer.setAttribute('aria-hidden', 'true');", $focusPosition);
        $this->assertNotFalse($focusPosition);
        $this->assertNotFalse($hiddenPosition);
        $this->assertLessThan($hiddenPosition, $focusPosition);
    }

    public function testRequestedMobileActionGroupsKeepTheirAlignment(): void
    {
        $passwordView = file_get_contents(APPPATH . 'Views/auth/change_password.php');
        $announcementView = file_get_contents(APPPATH . 'Views/admin/announcements/index.php');

        $this->assertStringContainsString('change-password-actions d-flex', $passwordView);
        $this->assertStringContainsString('.change-password-actions.d-flex.justify-content-end.gap-2:last-child', $passwordView);
        $this->assertStringContainsString('grid-template-columns: minmax(0, 0.72fr) minmax(0, 1.28fr) !important;', $passwordView);

        $this->assertStringContainsString('.announcement-actions-wrap', $announcementView);
        $this->assertStringContainsString('margin-left: auto !important;', $announcementView);
        $this->assertStringContainsString('width: auto !important;', $announcementView);
    }

    public function testNavigationDropdownsSupportClickAndTapAtEveryViewport(): void
    {
        $header = file_get_contents(APPPATH . 'Views/partials/header.php');
        $css = file_get_contents(FCPATH . 'assets/css/navigation.css');

        $this->assertStringContainsString('Dropdowns open on tap/click at all viewports', $header);
        $this->assertStringContainsString("var willOpen = !dropdown.classList.contains('mobile-open');", $header);
        $this->assertStringContainsString('function closeNavDropdowns(exceptDropdown)', $header);
        $this->assertStringContainsString('setNavDropdownState(dropdown, willOpen);', $header);
        $this->assertStringContainsString("e.target.closest('.nav-menu .dropdown')", $header);
        $this->assertStringContainsString("trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false')", $header);
        $this->assertStringContainsString('if (isOpen) closeNavDropdowns();', $header);
        $this->assertLessThan(
            strpos($header, "document.addEventListener('DOMContentLoaded'"),
            strpos($header, 'function closeNavDropdowns(exceptDropdown)')
        );
        $this->assertStringContainsString('.dropdown.mobile-open .dropdown-content', $css);
        $this->assertStringContainsString('.nav-menu .dropdown.mobile-open .dropdown-content', $css);

        $adminNav = file_get_contents(APPPATH . 'Views/partials/nav-admin.php');
        $this->assertSame(2, substr_count($adminNav, 'aria-haspopup="true"'));
        $this->assertSame(2, substr_count($adminNav, 'aria-expanded="false"'));
        $this->assertStringContainsString('id="managementDropdownMenu" role="menu"', $adminNav);
        $this->assertStringContainsString('id="recordsDropdownMenu" role="menu"', $adminNav);
    }
}
