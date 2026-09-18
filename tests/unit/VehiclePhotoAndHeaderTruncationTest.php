<?php

namespace Tests\Unit;

use App\Models\VehicleModel;
use CodeIgniter\Test\CIUnitTestCase;

class VehiclePhotoAndHeaderTruncationTest extends CIUnitTestCase
{
    public function testVehicleModelAllowsPhotoField(): void
    {
        $model = new VehicleModel();
        $this->assertContains('photo', $model->allowedFields);
    }

    public function testAddPhotoToVehiclesMigrationExists(): void
    {
        $migrationFile = APPPATH . 'Database/Migrations/2026-09-17-060000_AddPhotoToVehicles.php';
        $this->assertFileExists($migrationFile);
        require_once $migrationFile;
        $this->assertTrue(class_exists(\App\Database\Migrations\AddPhotoToVehicles::class));
    }

    public function testRegisterVehicleModalExistsInIndexView(): void
    {
        $viewContent = file_get_contents(APPPATH . 'Views/admin/vehicles/index.php');
        $this->assertStringContainsString('id="registerVehicleModal"', $viewContent);
        $this->assertStringContainsString('data-bs-target="#registerVehicleModal"', $viewContent);
        $this->assertStringContainsString('name="photo"', $viewContent);
        $this->assertStringContainsString('id="reg_veh_photo_preview"', $viewContent);
        $this->assertStringNotContainsString('id="registerVehicleCard"', $viewContent);
    }

    public function testEditVehicleHasPhotoUploadAndMultipart(): void
    {
        $editContent = file_get_contents(APPPATH . 'Views/admin/vehicles/edit.php');
        $this->assertStringContainsString('enctype="multipart/form-data"', $editContent);
        $this->assertStringContainsString('name="photo"', $editContent);
        $this->assertStringContainsString('name="remove_photo"', $editContent);
        $this->assertStringContainsString('id="edit_veh_photo_preview"', $editContent);
    }

    public function testGuestHeaderTruncationStyles(): void
    {
        $guestHeaderContent = file_get_contents(APPPATH . 'Views/templates/guest_header.php');
        $this->assertStringContainsString('text-overflow: ellipsis !important;', $guestHeaderContent);
        $this->assertStringContainsString('word-break: break-word !important;', $guestHeaderContent);
        $this->assertStringContainsString('.guest-header .logo-text h1', $guestHeaderContent);
        $this->assertStringContainsString('clamp(260px, 38vw, 560px)', $guestHeaderContent);
        $this->assertStringContainsString('clamp(150px, 56vw, 270px)', $guestHeaderContent);
    }

    public function testNavigationCssHeaderTruncation(): void
    {
        $cssContent = file_get_contents(FCPATH . 'assets/css/navigation.css');
        $this->assertStringContainsString('text-overflow: ellipsis !important;', $cssContent);
        $this->assertStringContainsString('word-break: break-word !important;', $cssContent);
        $this->assertStringContainsString('-webkit-line-clamp: 2 !important;', $cssContent);
        $this->assertStringContainsString('clamp(180px, 22vw, 380px)', $cssContent);
        $this->assertStringContainsString('clamp(160px, 58vw, 340px)', $cssContent);
        $this->assertStringContainsString('.drawer-brand-title', $cssContent);
    }

    public function testRegisterVehicleModalHasUnclippedDropdowns(): void
    {
        $viewContent = file_get_contents(APPPATH . 'Views/admin/vehicles/index.php');
        $this->assertStringContainsString('#registerVehicleModal', $viewContent);
        // Modal dialog must NOT be scrollable which causes dropdown clipping
        $this->assertStringNotContainsString('id="registerVehicleModal" tabindex="-1" aria-labelledby="registerVehicleModalLabel" aria-hidden="true">' . "\n" . '    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable', $viewContent);
        $this->assertStringContainsString('#registerVehicleModal .modal-body', $viewContent);
        $this->assertStringContainsString('overflow: visible !important;', $viewContent);

        $jsContent = file_get_contents(FCPATH . 'assets/js/autocomplete-search.js');
        $this->assertStringContainsString('dropdown-flipped', $jsContent);
        $this->assertStringContainsString('spaceBelow < 200', $jsContent);
    }

    public function testVehPhotoClearButtonsAndTheming(): void
    {
        $cssContent = file_get_contents(FCPATH . 'assets/css/navigation.css');
        $this->assertStringContainsString('.veh-photo-clear-btn', $cssContent);
        $this->assertStringContainsString('background-color: #ffffff !important;', $cssContent);
        $this->assertStringContainsString('color: #1e293b !important;', $cssContent);
        $this->assertStringContainsString('.veh-photo-clear-btn.has-photo', $cssContent);
        $this->assertStringContainsString('background-color: var(--primary, #dc2626) !important;', $cssContent);
        $this->assertStringContainsString('color: var(--on-primary, #ffffff) !important;', $cssContent);

        $indexContent = file_get_contents(APPPATH . 'Views/admin/vehicles/index.php');
        $this->assertStringContainsString('id="add_vt_photo_clear" class="veh-photo-clear-btn"', $indexContent);
        $this->assertStringContainsString('class="veh-photo-clear-btn <?= !empty($vtPhotoUrl) ? \'has-photo\' : \'\' ?>"', $indexContent);
        $this->assertStringContainsString('id="reg_veh_photo_clear" class="veh-photo-clear-btn"', $indexContent);
        $this->assertStringContainsString('file.size > 2 * 1024 * 1024', $indexContent);
        $this->assertStringContainsString('file.size > 5 * 1024 * 1024', $indexContent);

        $editContent = file_get_contents(APPPATH . 'Views/admin/vehicles/edit.php');
        $this->assertStringContainsString('id="edit_veh_photo_clear"', $editContent);
        $this->assertStringContainsString('file.size > 5 * 1024 * 1024', $editContent);
    }

    public function testSupportModalsSystemAlertExists(): void
    {
        $supportModalsContent = file_get_contents(APPPATH . 'Views/partials/support-modals.php');
        $this->assertStringContainsString('id="systemAlertModal"', $supportModalsContent);
        $this->assertStringContainsString('window.showSystemAlert = function', $supportModalsContent);
        $this->assertStringContainsString('window.alert = function', $supportModalsContent);
    }

    public function testVehicleInputClearButtonsAndMobileResponsiveness(): void
    {
        $indexContent = file_get_contents(APPPATH . 'Views/admin/vehicles/index.php');
        $this->assertStringContainsString('id="plate_number_clear"', $indexContent);
        $this->assertStringContainsString('id="operator_name_clear"', $indexContent);
        $this->assertStringContainsString('id="driver_name_clear"', $indexContent);
        $this->assertStringContainsString('clearVehicleInput(\'plate_number\')', $indexContent);
        $this->assertStringContainsString('clearVehicleInput(\'operator_name\')', $indexContent);
        $this->assertStringContainsString('clearVehicleInput(\'driver_name\')', $indexContent);
        $this->assertStringContainsString('window.toggleFieldClearBtn', $indexContent);
        $this->assertStringContainsString('window.clearVehicleInput', $indexContent);
        $this->assertStringContainsString('window.initVehicleInputClearBtns', $indexContent);

        // Manage Vehicle Types mobile responsiveness
        $this->assertStringContainsString('btn-action-edit', $indexContent);
        $this->assertStringContainsString('<span class="d-none d-sm-inline ms-1">Edit</span>', $indexContent);
        $this->assertStringContainsString('<span class="d-none d-sm-inline ms-1">Delete</span>', $indexContent);
        $this->assertStringContainsString('d-none d-sm-inline-block', $indexContent);
        $this->assertStringContainsString('rounded-circle d-inline-block d-sm-none flex-shrink-0', $indexContent);

        $this->assertStringContainsString('icon.innerHTML = \'<i class="bi bi-exclamation-circle-fill"', $indexContent);

        $editContent = file_get_contents(APPPATH . 'Views/admin/vehicles/edit.php');
        $this->assertStringContainsString('id="plate_number_clear"', $editContent);
        $this->assertStringContainsString('id="operator_name_clear"', $editContent);
        $this->assertStringContainsString('id="driver_name_clear"', $editContent);
        $this->assertStringContainsString('clearVehicleInput(\'plate_number\')', $editContent);
        $this->assertStringContainsString('clearVehicleInput(\'operator_name\')', $editContent);
        $this->assertStringContainsString('clearVehicleInput(\'driver_name\')', $editContent);
        $this->assertStringContainsString('icon.innerHTML = \'<i class="bi bi-exclamation-circle-fill"', $editContent);
    }

    public function testSettingsHeaderIconMobileResponsiveness(): void
    {
        $settingsContent = file_get_contents(APPPATH . 'Views/admin/settings/index.php');
        $this->assertStringContainsString('.settings-header-icon', $settingsContent);
        $this->assertStringContainsString('flex-shrink: 0 !important;', $settingsContent);
        $this->assertStringContainsString('min-width: 44px !important;', $settingsContent);
        $this->assertStringContainsString('align-items: flex-start !important;', $settingsContent);
    }

    public function testBrandMultiLineWrappingAcrossViews(): void
    {
        $settingsContent = file_get_contents(APPPATH . 'Views/admin/settings/index.php');
        $this->assertStringContainsString('.preview-header-bar .preview-brand h4', $settingsContent);
        $this->assertStringContainsString('-webkit-line-clamp: 2;', $settingsContent);
        $this->assertStringContainsString('word-break: break-word;', $settingsContent);

        $navCssContent = file_get_contents(FCPATH . 'assets/css/navigation.css');
        $this->assertStringContainsString('.drawer-brand-title', $navCssContent);
        $this->assertStringContainsString('-webkit-line-clamp: 2;', $navCssContent);
        $this->assertStringContainsString('word-break: break-word;', $navCssContent);
    }

    public function testEnhancedDashboardPosContrastAndCardLeaveHandling(): void
    {
        $dashboardContent = file_get_contents(APPPATH . 'Views/public/enhanced_dashboard.php');
        $this->assertStringContainsString('var posContrast = vMeta.contrast || (typeof getContrastTextColor === \'function\' ? getContrastTextColor(posColor) : \'#ffffff\');', $dashboardContent);
        $this->assertStringContainsString('existingCard.classList.remove(\'card-leave\');', $dashboardContent);
        $this->assertStringContainsString('cardToRemove.classList.contains(\'card-leave\')', $dashboardContent);
    }

    public function testSchedulesCacheKeyShortenedForDirectInvalidation(): void
    {
        $schedulesContent = file_get_contents(APPPATH . 'Controllers/Schedules.php');
        $this->assertStringContainsString('\'rt_sched_\' . substr(md5(', $schedulesContent);

        $baseControllerContent = file_get_contents(APPPATH . 'Controllers/BaseController.php');
        $this->assertStringContainsString('$cache->deleteMatching(\'rt_sched_*\');', $baseControllerContent);
    }

    public function testRealtimeVehiclePhotoInPlaceUpdateInEnhancedDashboard(): void
    {
        $dashboardContent = file_get_contents(APPPATH . 'Views/public/enhanced_dashboard.php');
        $this->assertStringContainsString('// 10. Update vehicle thumbnail / custom photo / fallback icon in place', $dashboardContent);
        $this->assertStringContainsString('var thumbBox = card.querySelector(\'.vehicle-thumb-box\');', $dashboardContent);
        $this->assertStringContainsString('currentImg.classList.add(\'vehicle-custom-photo\');', $dashboardContent);
        $this->assertStringContainsString('currentImg.classList.remove(\'vehicle-custom-photo\');', $dashboardContent);
        $this->assertStringContainsString('thumbBox.replaceChild(newI, currentImg);', $dashboardContent);
    }

    public function testRegisterVehicleModalNoExcessiveMinHeightToPreventScrollbar(): void
    {
        $indexContent = file_get_contents(APPPATH . 'Views/admin/vehicles/index.php');
        $this->assertStringContainsString('#registerVehicleModal .modal-dialog {', $indexContent);
        $this->assertStringContainsString('min-height: auto !important;', $indexContent);
        $this->assertStringNotContainsString('min-height: calc(100% - 1rem) !important;', $indexContent);
        $this->assertStringNotContainsString('min-height: calc(100dvh - 1rem) !important;', $indexContent);
    }

    public function testVehicleResolvedPhotoInViewsAndQueueSync(): void
    {
        $staffQueueContent = file_get_contents(APPPATH . 'Views/staff/queue/index.php');
        $this->assertStringContainsString('vehicle_resolved_photo($item, $vType)', $staffQueueContent);
        $this->assertStringContainsString('vehicle_resolved_photo($v, $vType)', $staffQueueContent);

        $sharedSchedContent = file_get_contents(APPPATH . 'Views/shared/schedules.php');
        $this->assertStringContainsString('vehicle_resolved_photo($s, $vType)', $sharedSchedContent);

        $publicSchedContent = file_get_contents(APPPATH . 'Views/public/schedules.php');
        $this->assertStringContainsString('vehicle_resolved_photo($schedule, $vType)', $publicSchedContent);

        $queueSyncContent = file_get_contents(FCPATH . 'js/queue-sync.js');
        $this->assertStringContainsString('item.photo_url || \'\'', $queueSyncContent);
        $this->assertStringContainsString('item.has_custom_photo ? \'custom\' : \'default\'', $queueSyncContent);
    }
}



