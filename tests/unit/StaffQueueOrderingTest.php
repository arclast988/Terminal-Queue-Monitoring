<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Guards the dispatcher-managed queue ordering and cancel/restore behavior.
 */
final class StaffQueueOrderingTest extends CIUnitTestCase
{
    public function testReorderEndpointRemainsDispatcherOnly(): void
    {
        $routes = file_get_contents(APPPATH . 'Config/Routes.php');
        $staffGroupStart = strpos($routes, '$routes->group(\'staff\', [\'filter\' => \'auth:staff\']');

        $this->assertNotFalse($staffGroupStart);
        $staffGroup = substr($routes, $staffGroupStart);
        $this->assertStringContainsString(
            '$routes->post(\'queue/reorder\', \'Staff\\Queue::reorder\')',
            $staffGroup
        );
    }

    public function testCanceledPositionIsPreservedAndRestoreReinsertsAtSavedSlot(): void
    {
        $controller = file_get_contents(APPPATH . 'Controllers/Staff/Queue.php');

        $this->assertStringContainsString('Keep the old position as the restore slot', $controller);
        $this->assertStringContainsString('$savedPosition = (int) ($queueItem[\'position\'] ?? 0);', $controller);
        $this->assertStringContainsString('array_splice($activeIds, $insertIndex, 0, [(int) $id]);', $controller);
        $this->assertStringContainsString('$insertIndex = max($boardingCount, $insertIndex);', $controller);
    }

    public function testQueuePositionIsCanonicalAndBoardingStaysFirst(): void
    {
        $model = file_get_contents(APPPATH . 'Models/QueueModel.php');
        $controller = file_get_contents(APPPATH . 'Controllers/Staff/Queue.php');

        $this->assertStringContainsString('$positionCmp = $aPosition <=> $bPosition;', $model);
        $this->assertStringContainsString('$finalOrder = array_merge($boardingIds, $waitingIds);', $controller);
        $this->assertStringContainsString('pg_advisory_xact_lock', $controller);
        $this->assertStringContainsString('$currentIds !== $submittedSet', $controller);
    }

    public function testDispatcherViewProvidesMobileQueueOrderControls(): void
    {
        $view = file_get_contents(APPPATH . 'Views/staff/queue/index.php');

        $this->assertStringContainsString('id="manageQueueModal"', $view);
        $this->assertStringContainsString('Manage Queue', $view);
        $this->assertStringContainsString('data-queue-move="up"', $view);
        $this->assertStringContainsString('data-queue-move="down"', $view);
        $this->assertStringContainsString("base_url('staff/queue/reorder')", $view);
        $this->assertStringContainsString('.queue-header-actions', $view);
        $this->assertStringContainsString('$queueOrderGroups = array_values($queueOrderGroups);', $view);
        $this->assertStringContainsString('queue-order-header', $view);
        $this->assertStringContainsString('queue-order-note', $view);
        $this->assertStringContainsString('queue-order-boarding-badge', $view);
        $this->assertStringContainsString('-webkit-text-fill-color: #ffffff !important;', $view);
        $this->assertStringContainsString('window.QueueOrderManager', $view);
        $this->assertStringContainsString('syncFromDocument: syncFromDocument', $view);
        $this->assertStringContainsString("window.QueueOrderManager.setStatus(queueId, 'canceled');", $view);
        $this->assertStringContainsString("window.QueueOrderManager.setStatus(queueId, 'waiting');", $view);
        $this->assertStringContainsString('window.QueueOrderManager.setStatus(id, data.status || status);', $view);
        $this->assertStringContainsString('data-queue-drag-handle', $view);
        $this->assertStringContainsString('draggable="false"', $view);
        $this->assertStringNotContainsString("modalEl.addEventListener('dragstart'", $view);
        $this->assertStringContainsString("modalEl.addEventListener('pointerdown'", $view);
        $this->assertStringContainsString('moveDraggedItem(pointerDrag.item, event.clientY);', $view);
        $this->assertStringContainsString("document.addEventListener('pointermove'", $view);
        $this->assertStringContainsString("document.addEventListener('pointerup'", $view);
        $this->assertStringNotContainsString('setPointerCapture(', $view);
        $this->assertStringNotContainsString("modalEl.addEventListener('lostpointercapture'", $view);
        $this->assertStringContainsString('visibleWaitingItems(list, item)', $view);
        $this->assertStringContainsString("window.addEventListener('blur', finishDragging);", $view);
        $this->assertStringContainsString('contain: layout paint;', $view);
        $this->assertStringContainsString("item.getAttribute('data-status') !== 'waiting'", $view);
        $this->assertStringContainsString('grid-template-columns: 36px minmax(0, 1fr);', $view);
        $this->assertStringContainsString('grid-template-columns: repeat(3, 44px);', $view);
        $this->assertStringContainsString('queue-order-details flex-grow-1', $view);
        $this->assertStringContainsString('modalBody.scrollTop = 0;', $view);
        $this->assertStringContainsString('add-queue-modal-empty', $view);
        $this->assertStringContainsString('add-queue-title-row', $view);
    }
}
