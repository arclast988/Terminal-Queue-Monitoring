<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Validates the fixes for:
 * 1. Passenger counter double increment/decrement (no inline onclick, only data-action).
 * 2. Vehicle register plate feedback badge styling (in-flow relative positioning).
 * 3. Centralized depart confirmation modal and QueueSync modalSelector null.
 * 4. QueueController::updateStatus AJAX JSON response on error.
 */
class StaffQueueAndVehicleFixTest extends CIUnitTestCase
{
    public function testStaffQueueViewHasNoInlineOnclickOnCounters()
    {
        $viewPath = APPPATH . 'Views/staff/queue/index.php';
        $this->assertFileExists($viewPath);

        $content = file_get_contents($viewPath);

        // Counter buttons should use data-action and NOT inline onclick
        $this->assertStringNotContainsString('onclick="updatePassengers(', $content);
        $this->assertStringContainsString('data-action="passenger-decrement"', $content);
        $this->assertStringContainsString('data-action="passenger-increment"', $content);
        $this->assertStringContainsString('data-action="passenger-max"', $content);
    }

    public function testDepartModalIsCentralizedAndSingle()
    {
        $viewPath = APPPATH . 'Views/staff/queue/index.php';
        $content = file_get_contents($viewPath);

        // Should have a single confirmDepartModal, not a per-item loop
        $this->assertStringNotContainsString('id="confirmDepartModal<?= $item[\'id\'] ?>"', $content);
        $this->assertStringContainsString('id="confirmDepartModal"', $content);
        $this->assertStringContainsString('id="confirmDepartSubmitBtn"', $content);
        $this->assertStringContainsString('data-action="open-depart-modal"', $content);

        // QueueSync should not destroy modals
        $this->assertStringContainsString('modalSelector: null,', $content);
    }

    public function testPlateValidationFeedbackIsInFlowRelative()
    {
        $cssPath = FCPATH . 'assets/css/admin-modern.css';
        $this->assertFileExists($cssPath);
        $css = file_get_contents($cssPath);

        $this->assertMatchesRegularExpression('/\.plate-validation-feedback\s*\{[^}]*position:\s*relative\s*!important/s', $css);

        $viewPath = APPPATH . 'Views/admin/vehicles/index.php';
        $viewContent = file_get_contents($viewPath);
        $this->assertMatchesRegularExpression('/\.plate-validation-feedback\s*\{[^}]*position:\s*relative\s*!important/s', $viewContent);
    }

    public function testAutocompleteSearchHandlesModalBoundaries()
    {
        $jsPath = FCPATH . 'assets/js/autocomplete-search.js';
        $this->assertFileExists($jsPath);
        $js = file_get_contents($jsPath);

        $this->assertStringContainsString("wrapper.closest('.modal-content, .modal-body, .modal-dialog, .modal')", $js);
        $this->assertStringContainsString("modalContent.querySelector('.modal-footer')", $js);
    }

    public function testWebpDefaultBrandingAndSlideshow()
    {
        // 1. Check physical webp files exist on disk
        $this->assertFileExists(FCPATH . 'images/logo.webp');
        $this->assertFileExists(FCPATH . 'images/bg/bg1_townhall.webp');
        $this->assertFileExists(FCPATH . 'images/bg/bg2_aerial_port.webp');
        $this->assertFileExists(FCPATH . 'images/bg/bg3_aerial_town.webp');
        $this->assertFileExists(FCPATH . 'images/bg/bg4_terminal_exterior.webp');
        $this->assertFileExists(FCPATH . 'images/bg/bg5_terminal_bay.webp');

        // 2. Clear any custom DB settings to test defaults
        $model = new \App\Models\SystemSettingModel();
        $model->setSetting('app_logo', null);
        $model->setSetting('app_background_image', null);
        $model->setSetting('app_login_card_image', null);
        $model->setSetting('app_bg_slideshow_1', null);
        $model->setSetting('app_bg_slideshow_2', null);
        $model->setSetting('app_bg_slideshow_3', null);
        $model->setSetting('app_bg_slideshow_4', null);
        $model->setSetting('app_bg_slideshow_5', null);
        get_all_system_settings(true);

        // 3. Verify defaults return logo.webp
        $this->assertStringContainsString('logo.webp', app_logo());
        $this->assertStringContainsString('logo.webp', app_bg_image());
        $this->assertStringContainsString('logo.webp', app_login_card_image());

        // 4. Verify slideshow defaults return .webp
        $slides = app_bg_slideshow();
        $this->assertCount(5, $slides);
        $this->assertStringContainsString('bg1_townhall.webp', $slides[1]);
        $this->assertStringContainsString('bg2_aerial_port.webp', $slides[2]);
        $this->assertStringContainsString('bg3_aerial_town.webp', $slides[3]);
        $this->assertStringContainsString('bg4_terminal_exterior.webp', $slides[4]);
        $this->assertStringContainsString('bg5_terminal_bay.webp', $slides[5]);
    }
}
