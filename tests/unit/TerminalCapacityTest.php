<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

final class TerminalCapacityTest extends CIUnitTestCase
{
    public function testTerminalCapacityStartsBlankAndRequiresPositiveWholeNumber(): void
    {
        $create = file_get_contents(APPPATH . 'Views/admin/terminals/create.php');
        $edit = file_get_contents(APPPATH . 'Views/admin/terminals/edit.php');
        $controller = file_get_contents(APPPATH . 'Controllers/Admin/Terminals.php');

        $this->assertStringContainsString("value=\"<?= old('capacity') ?>\"", $create);
        $this->assertStringNotContainsString("old('capacity', 0)", $create);
        $this->assertStringContainsString('min="1"', $create);
        $this->assertStringContainsString('step="1"', $create);
        $this->assertStringContainsString('min="1"', $edit);
        $this->assertStringContainsString('required|integer|greater_than[0]', $controller);
    }

    public function testTerminalCapacityIsEnforcedForQueueAddsAndRestores(): void
    {
        $queue = file_get_contents(APPPATH . 'Controllers/Staff/Queue.php');

        $this->assertStringContainsString('private function terminalCapacityError', $queue);
        $this->assertGreaterThanOrEqual(3, substr_count($queue, 'terminalCapacityError($db'));
        $this->assertStringContainsString("whereIn('queue.status', ['waiting', 'boarding'])", $queue);
        $this->assertStringContainsString('has reached its limit of', $queue);
    }

    public function testTerminalCapacityIsLabeledAsVehicles(): void
    {
        $index = file_get_contents(APPPATH . 'Views/admin/terminals/index.php');

        $this->assertStringContainsString("?> vehicles</span>", $index);
        $this->assertStringNotContainsString("terminal['capacity'] ?> pax", $index);
    }
}
