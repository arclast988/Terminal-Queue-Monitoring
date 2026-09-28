<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

class ThemeConsistencyAndClearButtonTest extends CIUnitTestCase
{
    public function testGuestDrawerThemeStartsAtTheActualDrawerBreakpoint(): void
    {
        $css = app_theme_css();

        $this->assertStringContainsString('@media (max-width: 900px)', $css);
        $this->assertStringNotContainsString(
            "@media (max-width: 1200px) {\n    .guest-header .nav-menu",
            $css
        );
    }

    /**
     * Test that app_theme_css outputs clear search button protection rules
     * ensuring it remains a small circular icon and never takes on primary button padding.
     */
    public function testClearSearchButtonProtectedInThemeCss(): void
    {
        $css = app_theme_css();

        $this->assertStringContainsString('.guest-clear-search-btn', $css);
        $this->assertStringContainsString('background: transparent !important;', $css);
        $this->assertStringContainsString('padding: 0 !important;', $css);
        $this->assertStringContainsString('width: 26px !important;', $css);
        $this->assertStringContainsString('height: 26px !important;', $css);
        $this->assertStringContainsString('border-radius: 50% !important;', $css);
        $this->assertStringContainsString('.search-bar button:not(.guest-clear-search-btn)', $css);
    }

    /**
     * Test that chip numbers/badges have proper contrast rules on hover and active states
     * preventing numbers from disappearing when hovered.
     */
    public function testChipCountBadgesHaveHighContrastAcrossStates(): void
    {
        $css = app_theme_css();

        // Guest filter and route chip badges
        $this->assertStringContainsString('.filter-chip:hover:not(.active) .chip-count', $css);
        $this->assertStringContainsString('.route-chip:hover:not(.active) .chip-count', $css);
        $this->assertStringContainsString('.filter-chip.active .chip-count', $css);
        $this->assertStringContainsString('.route-chip.active .chip-count', $css);

        // Staff route chips and departure filter chips
        $this->assertStringContainsString('body.staff-theme .route-chip:hover:not(.active) .chip-count', $css);
        $this->assertStringContainsString('body.staff-theme .dep-filter-btn[data-type="all"].active .dep-chip-count', $css);

        // Admin chips and filter buttons
        $this->assertStringContainsString('body.admin-theme .btn-filter.active .chip-count', $css);
        $this->assertStringContainsString('body.admin-theme .admin-chip:hover:not(.active) .chip-count', $css);
    }

    /**
     * Test that card theming consistently accents general modern and stat cards
     * across Guest, Dispatcher, and Admin portals.
     */
    public function testCardThemingConsistentAcrossPortals(): void
    {
        $css = app_theme_css();

        // Guest cards
        $this->assertStringContainsString('body.guest-theme .stat-card-modern:not([style*="--card-accent"]):not([data-vehicle-type])::before', $css);
        $this->assertStringContainsString('body.guest-theme .modern-card:not(.fare-section-card):not(.queue-card):not(.q-card):not([data-vehicle-type]):hover', $css);
        $this->assertStringContainsString('body.guest-theme .schedule-card:hover', $css);
        $this->assertStringContainsString('body.guest-theme .stat-card-link:not(.stat-card)', $css);

        // Staff cards
        $this->assertStringContainsString('body.staff-theme .stat-card-modern:not([style*="--card-accent"]):not([data-vehicle-type])::before', $css);
        $this->assertStringContainsString('body.staff-theme .modern-card:not(.fare-section-card):not(.queue-card):not(.q-card):not([data-vehicle-type]):hover', $css);
        $this->assertStringContainsString('body.staff-theme .stat-card-link:not(.stat-card)', $css);

        // Admin cards
        $this->assertStringContainsString('body.admin-theme .stat-card-modern:not([style*="--card-accent"]):not([data-vehicle-type]):not(.blue-accent):not(.gold-accent):not(.success-accent)::before', $css);
        $this->assertStringContainsString('body.admin-theme .modern-card:not(.fare-section-card):not(.queue-card):not(.q-card):not([data-vehicle-type]):hover', $css);
        $this->assertStringContainsString('body.admin-theme .stat-card-link:not(.stat-card)', $css);
    }

    /**
     * Test that vehicle type cards/badges and fare section cards are preserved
     * and NOT overridden by general card theme rules.
     */
    public function testVehicleTypeAndFareColorsPreserved(): void
    {
        $css = app_theme_css();

        // Check that selectors explicitly exclude fare section cards and vehicle queue cards
        $this->assertStringContainsString(':not(.fare-section-card)', $css);
        $this->assertStringContainsString(':not(.queue-card)', $css);
        $this->assertStringContainsString(':not(.q-card)', $css);
        $this->assertStringContainsString(':not([data-vehicle-type])', $css);
        $this->assertStringContainsString(':not([style*="--card-accent"])', $css);
    }

    /**
     * Test that guest footer dynamic theme elements track the primary brand color.
     */
    public function testGuestFooterTheming(): void
    {
        $css = app_theme_css();

        $this->assertStringContainsString('body.guest-theme footer .f-about h3', $css);
        $this->assertStringContainsString('body.guest-theme footer .f-links h4::after', $css);
        $this->assertStringContainsString('body.guest-theme footer .f-links a:hover', $css);
        $this->assertStringContainsString('body.guest-theme footer .f-links li i.fa-map-marker-alt', $css);
        $this->assertStringContainsString('body.guest-theme footer .f-links li i.fa-phone', $css);
        $this->assertStringContainsString('body.guest-theme footer .f-links li i.fa-envelope', $css);
    }

    /**
     * Test that hero search clear button has sufficient inset for "TRACK STATUS"
     * and search plate number badge matches schedule board.
     */
    public function testHeroClearButtonAlignmentAndPlateNumberConsistency(): void
    {
        $modernCss = file_get_contents(FCPATH . 'assets/css/modern-frontend.css');
        $this->assertStringContainsString('#guest-hero-clear-btn', $modernCss);
        $this->assertStringContainsString('align-self: center;', $modernCss);

        $enhancedDash = file_get_contents(APPPATH . 'Views/public/enhanced_dashboard.php');
        $this->assertStringContainsString('#guest-hero-clear-btn', $enhancedDash);
        $this->assertStringContainsString('padding-right: 40px !important;', $enhancedDash);

        $searchView = file_get_contents(APPPATH . 'Views/public/search.php');
        $this->assertStringContainsString('.plate-number {', $searchView);
        $this->assertStringContainsString('background: #f1f5f9;', $searchView);
        $this->assertStringContainsString('color: var(--text-main, #1e293b);', $searchView);
    }

    /**
     * Test that navbar brand logo text (.logo-text h1) properly follows var(--nav-text)
     * so it dynamically turns black or white without hardcoded white overrides.
     */
    public function testNavbarLogoTextBrandColorFollowsTheme(): void
    {
        $navCss = file_get_contents(FCPATH . 'assets/css/navigation.css');
        $this->assertStringContainsString('var(--nav-text, #ffffff) !important;', $navCss);
        $this->assertMatchesRegularExpression('/\.logo-text h1[^{]*\{[^}]*color:\s*var\(--nav-text/s', $navCss);
        $this->assertDoesNotMatchRegularExpression('/\.logo-text h1[^{]*\{[^}]*color:\s*#ffffff\s*!important/s', $navCss);

        $templatesHeader = file_get_contents(APPPATH . 'Views/templates/header.php');
        $this->assertStringContainsString('body.admin-theme .logo-text h1', $templatesHeader);
        $this->assertStringContainsString('color: var(--nav-text) !important;', $templatesHeader);

        $css = app_theme_css();
        $this->assertStringContainsString('body.admin-theme #site-header .logo-text h1', $css);
        $this->assertStringContainsString('body.staff-theme #site-header .logo-text h1', $css);
    }

    /**
     * Test that login hero copy helpers are available and login view displays clear high-contrast elements.
     */
    public function testLoginHeroCustomizationAndClearFooter(): void
    {
        $this->assertTrue(function_exists('login_headline'));
        $this->assertTrue(function_exists('login_headline_html'));
        $this->assertTrue(function_exists('login_subheadline'));
        $this->assertTrue(function_exists('login_kicker'));
        $this->assertTrue(function_exists('login_feature1_title'));
        $this->assertTrue(function_exists('login_feature1_desc'));
        $this->assertTrue(function_exists('login_feature2_title'));
        $this->assertTrue(function_exists('login_feature2_desc'));

        $loginView = file_get_contents(APPPATH . 'Views/auth/login.php');
        $this->assertStringContainsString('login_headline_html()', $loginView);
        $this->assertStringContainsString('login_subheadline()', $loginView);
        $this->assertStringContainsString('login_kicker()', $loginView);
        $this->assertStringContainsString('login_feature1_title()', $loginView);
        $this->assertStringContainsString('login_feature2_title()', $loginView);
        $this->assertStringContainsString('class="card-foot"', $loginView);
        $this->assertStringContainsString('Sign-in activity is recorded for security.', $loginView);
        $this->assertStringContainsString('#000000', $loginView);
    }

    /**
     * Test that vehicle type icons and colors properly map according to the system specifications.
     */
    public function testVehicleTypeIconsAndColorsSpecification(): void
    {
        $this->assertSame('fa-van-shuttle', vehicle_type_icon('van'));
        $this->assertSame('fa-truck-front', vehicle_type_icon('jeepney'));
        $this->assertSame('fa-bus', vehicle_type_icon('minibus'));
        $this->assertSame('fa-bus-simple', vehicle_type_icon('bus'));
        $this->assertSame('fa-motorcycle', vehicle_type_icon('tricycle'));
        $this->assertSame('fa-motorcycle', vehicle_type_icon('motorcycle'));
        $this->assertSame('fa-car', vehicle_type_icon('car'));
        $this->assertSame('fa-taxi', vehicle_type_icon('taxi'));

        $this->assertSame('bi-truck', vehicle_type_bi_icon('jeepney'));
        $this->assertSame('bi-bus-front', vehicle_type_bi_icon('minibus'));
        $this->assertSame('bi-car-front', vehicle_type_bi_icon('car'));
        $this->assertSame('bi-taxi-front', vehicle_type_bi_icon('taxi'));

        $this->assertSame('#c62828', vehicle_type_color('van'));
        $this->assertSame('#1565c0', vehicle_type_color('jeepney'));
        $this->assertSame('#2e7d32', vehicle_type_color('minibus'));
        $this->assertSame('#ea580c', vehicle_type_color('bus'));
    }

    /**
     * Test that header buttons and nav links maintain high contrast and readability on hover.
     */
    public function testHeaderButtonsMaintainContrastOnHover(): void
    {
        $navCss = file_get_contents(FCPATH . 'assets/css/navigation.css');

        // Verify .nav-menu a:hover and .dropbtn:hover do not use dark-on-dark red accent
        $this->assertDoesNotMatchRegularExpression('/\.nav-menu a:hover[^{]*\{[^}]*color:\s*var\(--nav-accent/s', $navCss);
        $this->assertDoesNotMatchRegularExpression('/\.dropbtn:hover[^{]*\{[^}]*color:\s*var\(--nav-accent/s', $navCss);

        // Verify .profile-trigger-name removes blurry text shadow
        $this->assertStringContainsString('text-shadow: none !important;', $navCss);
    }

    /**
     * Test that app_bg_slideshow_css supports dynamic slide counts (1, 2, 3, 5, etc.) and app_theme_css outputs universal background.
     */
    public function testDynamicSlideshowCssAndUniversalBackground(): void
    {
        $this->assertTrue(function_exists('app_bg_slideshow_css'));
        $this->assertTrue(function_exists('app_theme_css'));

        // 1 slide -> static background rule without @keyframes
        $css1 = app_bg_slideshow_css(['https://example.com/photo1.jpg'], 0.22);
        $this->assertStringContainsString('body::after', $css1);
        $this->assertStringContainsString('https://example.com/photo1.jpg', $css1);
        $this->assertStringNotContainsString('@keyframes terminalBgSlideshow', $css1);
        $this->assertStringNotContainsString('@keyframes palomponBgSlideshow', $css1);

        // 2 slides -> 12s duration animation with 2 keyframe stops
        $css2 = app_bg_slideshow_css(['https://example.com/p1.jpg', 'https://example.com/p2.jpg'], 0.20);
        $this->assertStringContainsString('animation: terminalBgSlideshow 12s infinite', $css2);
        $this->assertStringContainsString('@keyframes terminalBgSlideshow', $css2);
        $this->assertStringContainsString('https://example.com/p1.jpg', $css2);
        $this->assertStringContainsString('https://example.com/p2.jpg', $css2);

        // Universal background layer in app_theme_css()
        $themeCss = app_theme_css();
        $this->assertStringContainsString('body:not(.auth-page)::before', $themeCss);
        $this->assertStringContainsString('background-image:', $themeCss);
    }
}

