<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Tests verifying the removal of "FULL — Ready" from schedule views
 * and proper right-alignment of Route, Location, etc. in mobile view cards.
 */
class ScheduleAndMobileAlignmentTest extends CIUnitTestCase
{
    public function testSharedScheduleDoesNotContainFullReady()
    {
        $file = APPPATH . 'Views/shared/schedules.php';
        $this->assertFileExists($file);
        $content = file_get_contents($file);

        $this->assertStringNotContainsString('FULL — Ready', $content, 'Shared schedules view should not have FULL — Ready');
        $this->assertStringNotContainsString('full-badge', $content, 'Shared schedules view should not contain full-badge elements');
        $this->assertStringContainsString('shared-dep-time-', $content);
        $this->assertStringContainsString('shared-passengers-text-', $content);
        $this->assertStringNotContainsString("s['is_full'] ? 'display:none;' : ''", $content, 'Departure time and passenger count should not be hidden when full');
    }

    public function testPublicScheduleDoesNotContainFullReady()
    {
        $file = APPPATH . 'Views/public/schedules.php';
        $this->assertFileExists($file);
        $content = file_get_contents($file);

        $this->assertStringNotContainsString('FULL — Ready', $content, 'Public schedules view should not have FULL — Ready');
        $this->assertStringNotContainsString('sched-dep-full', $content, 'Public schedules view should not contain sched-dep-full elements');
        $this->assertStringContainsString('sched-dep-time-', $content);
        $this->assertStringContainsString('sched-passengers-', $content);
        $this->assertStringNotContainsString("schedule['is_full'] ? 'display:none;' : ''", $content, 'Departure time and passenger count should not be hidden when full');
    }

    public function testResponsiveCssAlignsRouteAndLocationRightOnMobile()
    {
        $file = FCPATH . 'assets/css/responsive.css';
        $this->assertFileExists($file);
        $content = file_get_contents($file);

        // Ensure Route and Location are NOT in the column-stacked selector
        $this->assertDoesNotMatchRegularExpression('/td\[data-label="Route"\][^\{]*\{[^}]*flex-direction:\s*column/s', $content, 'Route should not be stacked into a column');
        $this->assertDoesNotMatchRegularExpression('/td\[data-label="Location"\][^\{]*\{[^}]*flex-direction:\s*column/s', $content, 'Location should not be stacked into a column');

        // Ensure explicit right-aligned row rules exist for Route and Location
        $this->assertStringContainsString('td[data-label="Route"]', $content);
        $this->assertStringContainsString('td[data-label="Location"]', $content);
        $this->assertStringContainsString('.schedule-route-display', $content);
    }

    public function testPassengerColorHelperThresholds()
    {
        $this->assertTrue(function_exists('passenger_color_class'), 'passenger_color_class helper function should exist');

        // < 50% -> green
        $this->assertEquals('passenger-color-green', passenger_color_class(1, 12)); // 8.3%
        $this->assertEquals('passenger-color-green', passenger_color_class(5, 12)); // 41.7%
        $this->assertEquals('passenger-color-green', passenger_color_class(0, 12)); // 0%

        // 50% - 69% -> yellow
        $this->assertEquals('passenger-color-yellow', passenger_color_class(6, 12)); // 50%
        $this->assertEquals('passenger-color-yellow', passenger_color_class(8, 12)); // 66.7%

        // 70% - 89% -> orange
        $this->assertEquals('passenger-color-orange', passenger_color_class(9, 12)); // 75%
        $this->assertEquals('passenger-color-orange', passenger_color_class(10, 12)); // 83.3%

        // >= 90% -> red
        $this->assertEquals('passenger-color-red', passenger_color_class(11, 12)); // 91.7%
        $this->assertEquals('passenger-color-red', passenger_color_class(12, 12)); // 100%
    }

    public function testCountdownTimerFourTiers()
    {
        $staffFile = APPPATH . 'Views/staff/queue/index.php';
        $contentStaff = file_get_contents($staffFile);
        $this->assertStringContainsString('cd-green', $contentStaff);
        $this->assertStringContainsString('cd-yellow', $contentStaff);
        $this->assertStringContainsString('cd-orange', $contentStaff);
        $this->assertStringContainsString('cd-red', $contentStaff);

        $dashboardFile = APPPATH . 'Views/public/enhanced_dashboard.php';
        $contentDashboard = file_get_contents($dashboardFile);
        $this->assertStringContainsString('cd-green', $contentDashboard);
        $this->assertStringContainsString('cd-yellow', $contentDashboard);
        $this->assertStringContainsString('cd-orange', $contentDashboard);
        $this->assertStringContainsString('cd-red', $contentDashboard);
    }

    public function testGuestMobileStatsUseTwoCardsThenCenteredThirdCard(): void
    {
        $dashboardFile = APPPATH . 'Views/public/enhanced_dashboard.php';
        $content = file_get_contents($dashboardFile);

        $this->assertStringContainsString('stats-grid guest-stats-grid', $content);
        $this->assertStringContainsString('grid-template-columns: repeat(2, minmax(0, 1fr)) !important;', $content);
        $this->assertStringContainsString('.guest-stats-grid .stat-card:nth-child(3)', $content);
        $this->assertStringContainsString('grid-column: 1 / -1;', $content);
        $this->assertStringContainsString('width: calc(50% - 4px) !important;', $content);
    }
}
