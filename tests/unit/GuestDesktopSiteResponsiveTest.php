<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class GuestDesktopSiteResponsiveTest extends CIUnitTestCase
{
    public function testGuestDrawerHasItsOwnVisibleCloseControl(): void
    {
        $header = file_get_contents(APPPATH . 'Views/templates/guest_header.php');
        $css = file_get_contents(FCPATH . 'assets/css/guest-shell.css');

        $this->assertStringContainsString('class="mobile-nav-close"', $header);
        $this->assertStringContainsString('onclick="closeMenu()"', $header);
        $this->assertStringContainsString('function setMenuOpen(shouldOpen)', $header);
        $this->assertStringContainsString('aria-expanded="false"', $header);
        $this->assertStringContainsString('width: min(340px, 86vw);', $css);
        $this->assertStringContainsString('height: 100dvh;', $css);
        $this->assertStringContainsString('.guest-header .mobile-nav-close', $css);
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
