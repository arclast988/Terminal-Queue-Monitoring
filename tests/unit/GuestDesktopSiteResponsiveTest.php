<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class GuestDesktopSiteResponsiveTest extends CIUnitTestCase
{
    public function testGuestDrawerUsesItsOwnResponsiveCloseControl(): void
    {
        $header = file_get_contents(APPPATH . 'Views/templates/guest_header.php');
        $css = file_get_contents(FCPATH . 'assets/css/guest-shell.css');

        $this->assertStringContainsString('class="guest-nav-close-btn"', $header);
        $this->assertStringContainsString('function setMenuOpen(shouldOpen)', $header);
        $this->assertStringContainsString('aria-expanded="false"', $header);
        $this->assertStringContainsString("if (icon) icon.className = 'fas fa-bars';", $header);
        $this->assertStringContainsString("m.addEventListener('transitionend', finishClose, { once: true })", $header);
        $this->assertStringContainsString('width: min(340px, 86vw);', $css);
        $this->assertStringContainsString('width: min(300px, 82vw);', $css);
        $this->assertStringContainsString('height: calc(100dvh - 42px);', $css);
        $this->assertStringContainsString('transform: translate3d(100%, 0, 0);', $css);
        $this->assertStringContainsString('transform: translate3d(0, 0, 0);', $css);
        $this->assertStringContainsString('.guest-header .nav-menu > a', $css);
        $this->assertStringContainsString('display: flex !important;', $css);
        $this->assertStringContainsString('.guest-header .guest-nav-close-btn', $css);
        $this->assertStringContainsString('.guest-header .mobile-toggle[aria-expanded="true"]', $css);
        $this->assertStringContainsString('top: 42px;', $css);
        $this->assertStringContainsString('top: 38px;', $css);
        $this->assertStringContainsString('top: 36px;', $css);
        $this->assertStringContainsString('font-variant-numeric: tabular-nums;', $css);
        $this->assertStringContainsString('flex: 0 0 16px;', $css);
        $this->assertStringContainsString('font-size: 14.5px;', $css);
        $this->assertStringContainsString('@media (min-width: 901px) and (max-width: 1200px)', $css);
        $this->assertStringContainsString('@media (max-width: 900px)', $css);
        $this->assertStringContainsString('.guest-header .nav-menu > a', $css);
        $this->assertStringContainsString('display: none !important;', $css);
        $this->assertStringContainsString('if (window.innerWidth > 900) closeMenu();', $header);
    }

    public function testGuestFooterAdaptsToDesktopSitePhoneWidths(): void
    {
        $css = file_get_contents(FCPATH . 'assets/css/guest-shell.css');

        $this->assertStringContainsString('@media (max-width: 1200px)', $css);
        $this->assertStringContainsString('grid-template-columns: repeat(2, minmax(0, 1fr));', $css);
        $this->assertStringContainsString('@media (max-width: 640px)', $css);
        $this->assertStringContainsString('grid-template-columns: minmax(0, 1fr);', $css);
    }

    public function testScheduleTableUsesCardLayoutBeforeItCanOverflow(): void
    {
        $schedules = file_get_contents(APPPATH . 'Views/public/schedules.php');
        $responsiveStart = strpos($schedules, '@media (max-width: 1200px)');
        $mobileStart = strpos($schedules, '@media (max-width: 768px)', $responsiveStart);

        $this->assertNotFalse($responsiveStart);
        $this->assertNotFalse($mobileStart);

        $desktopSiteRules = substr($schedules, $responsiveStart, $mobileStart - $responsiveStart);
        $this->assertStringContainsString('.schedule-table thead', $desktopSiteRules);
        $this->assertStringContainsString('display: none;', $desktopSiteRules);
        $this->assertStringContainsString('overflow-x: hidden;', $desktopSiteRules);
    }
}
