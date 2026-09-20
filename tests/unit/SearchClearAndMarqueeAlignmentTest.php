<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

class SearchClearAndMarqueeAlignmentTest extends CIUnitTestCase
{
    public function testGuestMarqueeAlignmentAndBoldStyles(): void
    {
        $html = view('templates/guest_header', [
            'title' => 'Home',
        ]);
        $css = file_get_contents(FCPATH . 'assets/css/guest-shell.css');

        $this->assertStringContainsString('assets/css/guest-shell.css', $html);
        $this->assertStringContainsString('display: inline-flex;', $css);
        $this->assertStringContainsString('align-items: center;', $css);
        $this->assertStringContainsString('line-height: 42px !important;', $css);
        $this->assertStringContainsString('white-space: nowrap !important;', $css);
        $this->assertStringContainsString('font-weight: 800 !important;', $css);
        $this->assertStringContainsString('min-height: 42px;', $css);
        $this->assertStringContainsString('color: #ffffff !important;', $css);
    }

    public function testSharedFaresClearButtonHiddenWhenEmpty(): void
    {
        $html = view('shared/fares', [
            'title'                 => 'Route Fares',
            'van_routes'            => [],
            'jeepney_routes'        => [],
            'minibus_routes'        => [],
            'vehicleTypes'          => [],
            'routesByType'          => [],
            'announcements'         => [],
            'terminals'             => [],
            'discounts'             => [],
            'all_locations'         => [],
            'unassignedRoutes'      => [],
            'availableDestinations' => [],
            'allActiveRoutes'       => [],
        ]);

        // Verify that #clearFareSearch has inline display: none !important; initially
        $this->assertStringContainsString('id="clearFareSearch"', $html);
        $this->assertStringContainsString('style="display: none !important;"', $html);
        $this->assertStringContainsString('toggleFareClearBtn', $html);
        // Verify fare-search-group has position: relative !important to keep clear button inside capsule
        $this->assertStringContainsString('position: relative !important;', $html);
        $this->assertStringContainsString('placeholder="Search routes and fares..."', $html);
        $this->assertStringContainsString('class="fare-filter-layout"', $html);
        $this->assertStringContainsString('border-radius: 12px !important;', $html);
        $this->assertStringContainsString('box-shadow: 0 0 0 2px', $html);
        $this->assertStringContainsString('.discount-card-actions', $html);
        $this->assertStringContainsString('justify-content: flex-end !important;', $html);
    }

    public function testPublicSchedulesHasClearSearchButton(): void
    {
        $content = file_get_contents(APPPATH . 'Views/public/schedules.php');
        $this->assertNotFalse($content);
        $this->assertStringContainsString('id="guest-schedule-clear-btn"', $content);
        $this->assertStringContainsString('clearGuestScheduleSearch()', $content);
        $this->assertStringContainsString('toggleGuestScheduleClear', $content);
    }

    public function testStaffQueueVehicleModalHasClearSearchButton(): void
    {
        $html = view('staff/queue/index', [
            'title'               => 'Vehicle Queue Management',
            'activeVehicleType'   => 'all',
            'availableCount'      => 1,
            'queuedCount'         => 0,
            'departedCount'       => 0,
            'totalCount'          => 1,
            'user'                => ['name' => 'Staff Test', 'role' => 'staff'],
            'vehicles'            => [
                [
                    'id'           => 1,
                    'plate_number' => 'ABC 1234',
                    'type'         => 'van',
                    'driver_name'  => 'Test Driver',
                    'route_label'  => 'Palompon - Ormoc',
                ]
            ],
            'queueEntries'        => [],
            'routes'              => [],
            'terminals'           => [],
            'vehicleTypes'        => [],
        ]);

        $this->assertStringContainsString('id="clearVehicleModalSearch"', $html);
        $this->assertStringContainsString('clearVehicleModalSearch()', $html);
        $this->assertStringContainsString('toggleVehicleModalClear', $html);
    }

    public function testModernFrontendCssStrictClearButtonEnforcement(): void
    {
        $css = file_get_contents(FCPATH . 'assets/css/modern-frontend.css');
        $this->assertNotFalse($css);
        $this->assertStringContainsString('.btn-clear-search[style*="display: none"]', $css);
        $this->assertStringContainsString('.fare-search-clear[style*="display: none"]', $css);
        $this->assertStringContainsString('.guest-clear-search-btn[style*="display: none"]', $css);
        $this->assertStringContainsString('display: none !important;', $css);
        // Verify no scale(1.1) animation remains on search clear button hovers
        $this->assertStringNotContainsString('.btn-clear-search:hover {' . "\r\n" . '    color: #475569 !important;' . "\r\n" . '    transform: translateY(-50%) scale(1.1);', $css);
        $this->assertStringNotContainsString('.fare-search-clear:hover {' . "\r\n" . '    color: #475569 !important;' . "\r\n" . '    transform: translateY(-50%) scale(1.1);', $css);
        $this->assertStringNotContainsString('.guest-clear-search-btn:hover {' . "\r\n" . '    color: #475569 !important;' . "\r\n" . '    transform: translateY(-50%) scale(1.1);', $css);
    }

    public function testAutocompleteSearchHasUniversalClearButton(): void
    {
        $js = file_get_contents(FCPATH . 'assets/js/autocomplete-search.js');
        $this->assertNotFalse($js);
        $this->assertStringContainsString('.autocomplete-clear-btn', $js);
        $this->assertStringContainsString('Clear selection', $js);
        $this->assertStringContainsString('Clear input', $js);
    }
}
