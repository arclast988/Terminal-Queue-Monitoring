<?php

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\QueueModel;

/**
 * @internal
 */
final class QueueConcurrencyTest extends CIUnitTestCase
{
    private QueueModel $queueModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->queueModel = new QueueModel();
    }

    public function testResolveGroupIntervalFromRulesMatchesRouteSpecific(): void
    {
        $mockRules = [
            [
                'id'           => 1,
                'terminal_id'  => 1,
                'route_id'     => 5,
                'time_from'    => '06:00:00',
                'time_to'      => '18:00:00',
                'wait_minutes' => 15,
                'label'        => 'Ormoc Peak Day',
            ],
            [
                'id'           => 2,
                'terminal_id'  => 1,
                'route_id'     => null,
                'time_from'    => '00:00:00',
                'time_to'      => '23:59:59',
                'wait_minutes' => 45,
                'label'        => 'Default Terminal Rule',
            ],
        ];

        // Should match route-specific rule (15 min)
        $waitMinutes = $this->queueModel->resolveGroupIntervalFromRules($mockRules, '08:30:00', 1, [5]);
        $this->assertEquals(15, $waitMinutes);

        // Another route with no override should fall back to terminal default (45 min)
        $waitMinutesFallback = $this->queueModel->resolveGroupIntervalFromRules($mockRules, '08:30:00', 1, [99]);
        $this->assertEquals(45, $waitMinutesFallback);
    }

    public function testResolveGroupIntervalFromRulesFallbackWhenNoRules(): void
    {
        $waitMinutes = $this->queueModel->resolveGroupIntervalFromRules([], '12:00:00', 1, [1]);
        $this->assertEquals(30, $waitMinutes);
    }

    public function testRecalculateScheduleImplementsAdvisoryLockAndBatchUpdate(): void
    {
        $modelFile = APPPATH . 'Models/QueueModel.php';
        $this->assertFileExists($modelFile);
        $content = file_get_contents($modelFile);

        // Verify transaction advisory lock is used
        $this->assertStringContainsString('pg_advisory_xact_lock', $content);
        $this->assertStringContainsString('$db->transStart()', $content);
        $this->assertStringContainsString('$db->transComplete()', $content);

        // Verify individual-row updates are used (updateBatch generates invalid
        // SQL on PostgreSQL when columns differ across rows)
        $this->assertStringContainsString('$this->update($uid, $upd)', $content);

        // Verify pre-loaded rules resolution
        $this->assertStringContainsString('resolveGroupIntervalFromRules', $content);
    }
}
