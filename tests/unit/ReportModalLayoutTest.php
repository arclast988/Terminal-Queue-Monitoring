<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ReportModalLayoutTest extends CIUnitTestCase
{
    public function testReportPartialsUseSharedScrollableLayout(): void
    {
        $partials = [
            APPPATH . 'Views/admin/modals/report_filter.php',
            APPPATH . 'Views/admin/modals/log_report_filter.php',
        ];

        foreach ($partials as $partial) {
            $this->assertFileExists($partial);
            $content = file_get_contents($partial);

            $this->assertStringContainsString('report-filter-modal', $content);
            $this->assertStringContainsString('report-filter-form', $content);
            $this->assertStringContainsString('report-filter-body', $content);
            $this->assertStringContainsString('report-filter-presets', $content);
            $this->assertStringContainsString('report-filter-footer', $content);
        }
    }

    public function testReportModalCssKeepsBodyScrollableAndFooterVisible(): void
    {
        $cssFile = FCPATH . 'assets/css/navigation.css';
        $content = file_get_contents($cssFile);

        $this->assertStringContainsString('.report-filter-modal .report-filter-body', $content);
        $this->assertStringContainsString('overflow-y: auto !important;', $content);
        $this->assertStringContainsString('.report-filter-modal .report-filter-presets', $content);
        $this->assertStringContainsString('grid-template-columns: repeat(2, minmax(0, 1fr));', $content);
        $this->assertStringNotContainsString("#logReportFilterModal .modal-body,\n#logReportFilterModal .mb-3", $content);
    }
}
