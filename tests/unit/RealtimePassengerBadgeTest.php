<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class RealtimePassengerBadgeTest extends CIUnitTestCase
{
    public function testStaffQueueViewHasFullBadgeWrapAndDataCapacity(): void
    {
        $viewFile = APPPATH . 'Views/staff/queue/index.php';
        $this->assertFileExists($viewFile);
        $content = file_get_contents($viewFile);

        // Ensure data-capacity attribute exists on passenger count span
        $this->assertStringContainsString('data-capacity=', $content);

        // Ensure full-badge- wrapper with dynamic ID exists
        $this->assertStringContainsString('id="full-badge-<?= $item[\'id\'] ?>"', $content);
        $this->assertStringContainsString('class="full-badge-wrap"', $content);
    }

    public function testDebouncePassengersModuleHandlesFullBadgeToggling(): void
    {
        $jsFile = FCPATH . 'js/debounce-passengers.js';
        $this->assertFileExists($jsFile);
        $content = file_get_contents($jsFile);

        // Ensure getBadge searches for full-badge- container
        $this->assertStringContainsString("'full-badge-' + id", $content);
        $this->assertStringContainsString(".full-badge-wrap", $content);

        // Ensure updateUI toggles display based on capacity
        $this->assertStringContainsString("badge.style.display = isFull ? 'block' : 'none'", $content);
    }

    public function testQueueSyncModuleHandlesFullBadgeToggling(): void
    {
        $jsFile = FCPATH . 'js/queue-sync.js';
        $this->assertFileExists($jsFile);
        $content = file_get_contents($jsFile);

        // Ensure getBadge searches for full-badge- container in queue-sync
        $this->assertStringContainsString("'full-badge-' + id", $content);
        $this->assertStringContainsString(".full-badge-wrap", $content);

        // Ensure updatePassengerUI toggles display based on capacity
        $this->assertStringContainsString("badge.style.display = isFull ? 'block' : 'none'", $content);
    }
}
