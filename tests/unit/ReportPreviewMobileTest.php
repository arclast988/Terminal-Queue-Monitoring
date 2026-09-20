<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

class ReportPreviewMobileTest extends CIUnitTestCase
{
    public function testActivityLogReportHasReadableMobileTableAndReliableCloseFallback(): void
    {
        $view = file_get_contents(APPPATH . 'Views/admin/logs/print_logs.php');

        $this->assertNotFalse($view);
        $this->assertStringContainsString('class="report-table-wrapper"', $view);
        $this->assertStringContainsString('class="mobile-scroll-hint"', $view);
        $this->assertStringContainsString('min-width: 760px;', $view);
        $this->assertStringContainsString('function closeReportPreview()', $view);
        $this->assertStringContainsString("base_url('admin/logs')", $view);
        $this->assertStringNotContainsString('onclick="window.close()"', $view);
    }

    public function testDepartureHistoryReportHasReliableCloseFallback(): void
    {
        $view = file_get_contents(APPPATH . 'Views/admin/history/print_history.php');

        $this->assertNotFalse($view);
        $this->assertStringContainsString('function closeReportPreview()', $view);
        $this->assertStringContainsString("base_url('admin/history')", $view);
        $this->assertStringNotContainsString('onclick="window.close()"', $view);
    }
}
