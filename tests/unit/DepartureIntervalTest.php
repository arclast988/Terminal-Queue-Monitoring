<?php

namespace Tests\Unit;

use App\Models\DepartureRuleModel;
use App\Models\QueueModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

/**
 * Exercise the real models against an isolated database, including destinations
 * whose representative route has no active vehicle.
 */
final class DepartureIntervalTest extends CIUnitTestCase
{
    private BaseConnection $intervalDb;
    private QueueModel $queue;

    protected function setUp(): void
    {
        parent::setUp();
        $this->intervalDb = Database::connect([
            'DBDriver' => 'SQLite3',
            'database' => ':memory:',
            'DBPrefix' => '',
            'DBDebug' => true,
        ], false);
        $this->intervalDb->query('CREATE TABLE routes (
            id INTEGER PRIMARY KEY, terminal_id INTEGER, destination TEXT,
            vehicle_type TEXT, status TEXT, created_at TEXT
        )');
        $this->intervalDb->query('CREATE TABLE departure_rules (
            id INTEGER PRIMARY KEY, terminal_id INTEGER, route_id INTEGER,
            time_from TEXT, time_to TEXT, wait_minutes INTEGER, label TEXT,
            created_at TEXT, updated_at TEXT
        )');
        $this->intervalDb->query('CREATE TABLE queue (
            id INTEGER PRIMARY KEY AUTOINCREMENT, vehicle_id INTEGER,
            route_id INTEGER, status TEXT, position INTEGER, arrival_time TEXT,
            estimated_departure TEXT, departure_time TEXT, current_passengers INTEGER,
            driver_name TEXT, operator_name TEXT, plate_number TEXT
        )');
        $this->intervalDb->table('routes')->insertBatch([
            ['id' => 1, 'terminal_id' => 1, 'destination' => 'ORMOC', 'vehicle_type' => 'van'],
            ['id' => 2, 'terminal_id' => 1, 'destination' => 'ORMOC', 'vehicle_type' => 'minibus'],
            ['id' => 3, 'terminal_id' => 1, 'destination' => 'PALOMPON', 'vehicle_type' => 'van'],
            ['id' => 4, 'terminal_id' => 2, 'destination' => 'ORMOC', 'vehicle_type' => 'van'],
        ]);
        $this->addRule(1, 1, 1, 20);
        $this->addRule(2, 1, null, 40);
        $this->addRule(3, 2, null, 60);
        $this->queue = new QueueModel($this->intervalDb);
    }

    protected function tearDown(): void
    {
        $this->intervalDb->close();
        parent::tearDown();
    }

    public function testDestinationRuleAppliesToEveryVehicleTypeAndLookupPath(): void
    {
        $rules = new DepartureRuleModel($this->intervalDb);
        foreach ([1, 2] as $routeId) {
            $this->assertSame(20, $rules->getWaitMinutesForTime('10:00:00', 1, $routeId));
        }
        $this->assertSame(40, $rules->getWaitMinutesForTime('10:00:00', 1, 3));
        $this->assertSame(60, $rules->getWaitMinutesForTime('10:00:00', 2, 4));
        $this->assertSame(60, $rules->getWaitMinutesForTime('10:00:00', 2, 1));
    }

    public function testOnlyMinibusesStillReceiveTwentyMinuteSlots(): void
    {
        for ($position = 1; $position <= 4; $position++) {
            $this->addVehicle(2, $position);
        }
        $before = time();
        $this->queue->recalculateSchedule(2);
        $rows = $this->activeRows();
        $this->assertSame([1, 2, 3, 4], array_map('intval', array_column($rows, 'position')));
        $this->assertGaps($rows, [20, 20, 20]);
        $this->assertEqualsWithDelta($before + 1200, strtotime($rows[0]['estimated_departure']), 2);
    }

    public function testMixedVehicleTypesShareOneTwentyMinuteLine(): void
    {
        $this->addVehicle(2, 1, '2026-10-03 10:20:00');
        $this->addVehicle(1, 2);
        $this->addVehicle(2, 3);
        $this->queue->recalculateSchedule();
        $this->assertGaps($this->activeRows(), [20, 20]);
    }

    public function testAddingAndRepeatedRecalculationDoNotRestartTheCountdown(): void
    {
        $head = $this->addVehicle(2, 1, '2026-10-03 10:20:00');
        $this->queue->recalculateSchedule();
        $this->addVehicle(2, 2);
        $this->queue->recalculateSchedule();
        $this->queue->recalculateSchedule();
        $this->assertSame('2026-10-03 10:20:00', $this->queue->find($head)['estimated_departure']);
        $this->assertGaps($this->activeRows(), [20]);
    }

    public function testBoardingKeepsTheAssignedSlotInsteadOfAddingAnotherInterval(): void
    {
        $head = $this->addVehicle(2, 1, '2026-10-03 10:20:00');
        $this->addVehicle(2, 2);
        $this->queue->recalculateSchedule();
        $this->queue->update($head, ['status' => 'boarding']);
        $this->queue->recalculateSchedule();
        $this->queue->recalculateSchedule();
        $this->assertSame('2026-10-03 10:20:00', $this->queue->find($head)['estimated_departure']);
        $this->assertGaps($this->activeRows(), [20]);
    }

    public function testReorderingPreservesTheLineAnchorAndUsesTheSavedOrder(): void
    {
        $first = $this->addVehicle(2, 1, '2026-10-03 10:20:00');
        $second = $this->addVehicle(2, 2, '2026-10-03 10:40:00');
        $this->queue->update($first, ['position' => 2]);
        $this->queue->update($second, ['position' => 1]);
        $this->queue->recalculateSchedule();
        $rows = $this->activeRows();
        $this->assertSame($second, (int) $rows[0]['id']);
        $this->assertSame('2026-10-03 10:20:00', $rows[0]['estimated_departure']);
        $this->assertGaps($rows, [20]);
    }

    public function testCancelAndRestoreCompactTheSlotsWithoutLeavingFortyMinuteGaps(): void
    {
        $this->addVehicle(2, 1, '2026-10-03 10:20:00');
        $middle = $this->addVehicle(2, 2, '2026-10-03 10:40:00');
        $last = $this->addVehicle(2, 3, '2026-10-03 11:00:00');
        $this->queue->update($middle, ['status' => 'canceled', 'estimated_departure' => null]);
        $this->queue->recalculateSchedule();
        $this->assertGaps($this->activeRows(), [20]);
        $this->queue->update($last, ['position' => 3]);
        $this->queue->update($middle, ['status' => 'waiting', 'position' => 2]);
        $this->queue->recalculateSchedule();
        $this->assertGaps($this->activeRows(), [20, 20]);
    }

    public function testDepartingRepresentativeVehicleDoesNotChangeTheRemainingInterval(): void
    {
        $head = $this->addVehicle(1, 1, '2026-10-03 10:20:00');
        $this->addVehicle(2, 2, '2026-10-03 10:40:00');
        $this->addVehicle(2, 3, '2026-10-03 11:00:00');
        $this->queue->update($head, ['status' => 'departed', 'position' => 0, 'departure_time' => '2026-10-03 10:20:00']);
        $this->queue->recalculateSchedule();
        $rows = $this->activeRows();
        $this->assertSame('2026-10-03 10:40:00', $rows[0]['estimated_departure']);
        $this->assertGaps($rows, [20]);
    }

    public function testEditingRulesResetsWaitingSlotsToTheNewInterval(): void
    {
        $this->addVehicle(2, 1, '2026-10-03 10:40:00');
        $this->addVehicle(2, 2, '2026-10-03 11:20:00');
        $this->intervalDb->table('departure_rules')->where('id', 1)->update(['wait_minutes' => 20]);
        $before = time();
        $this->queue->recalculateSchedule(null, true);
        $rows = $this->activeRows();
        $this->assertEqualsWithDelta($before + 1200, strtotime($rows[0]['estimated_departure']), 2);
        $this->assertGaps($rows, [20]);
    }

    public function testTimeWindowBoundariesAndMidnightUseTheConfiguredRules(): void
    {
        $this->intervalDb->table('departure_rules')->where('id', 1)->update(['time_to' => '12:00:00']);
        $this->addVehicle(2, 1, '2026-10-03 11:40:00');
        $this->addVehicle(2, 2);
        $this->addVehicle(2, 3);
        $this->queue->recalculateSchedule();
        $this->assertGaps($this->activeRows(), [20, 40]);
        $this->intervalDb->table('queue')->emptyTable();
        $this->intervalDb->table('departure_rules')->where('id', 1)->update(['time_to' => '23:59:00']);
        $this->addVehicle(2, 1, '2026-10-03 23:59:30');
        $this->addVehicle(2, 2);
        $this->addVehicle(2, 3);
        $this->queue->recalculateSchedule();
        $this->assertGaps($this->activeRows(), [20, 20]);
        $this->assertSame('2026-10-04 00:19:30', $this->activeRows()[1]['estimated_departure']);
    }

    public function testLegacyOverlapsResolveConsistentlyAcrossQueueAndDirectLookup(): void
    {
        $this->addRule(4, 1, 2, 40, '2026-10-02 10:00:00');
        $this->intervalDb->table('departure_rules')->where('id', 1)->update(['updated_at' => '2026-10-03 10:00:00']);
        $rules = new DepartureRuleModel($this->intervalDb);
        $allRules = $rules->findAll();
        $this->assertSame(20, $rules->getWaitMinutesForTime('10:00:00', 1, 2));
        $this->assertSame(20, $this->queue->resolveGroupIntervalFromRules(array_reverse($allRules), '10:00:00', 1, [2, 1]));
    }

    public function testDeploymentRepairsStoredSlotsAndPreservesDepartureHistory(): void
    {
        $this->addVehicle(2, 1, '2026-10-03 10:20:00');
        $this->addVehicle(2, 2, '2026-10-03 11:00:00');
        $departed = $this->addVehicle(2, 0, '2026-10-03 09:00:00');
        $this->queue->update($departed, ['status' => 'departed', 'departure_time' => '2026-10-03 09:00:00']);
        $history = $this->queue->find($departed);
        require_once APPPATH . 'Database/Migrations/2026-10-03-120000_RecalculateDepartureIntervals.php';
        $migration = new \App\Database\Migrations\RecalculateDepartureIntervals(Database::forge($this->intervalDb));
        $migration->up();
        $rows = $this->activeRows();
        $this->assertSame('2026-10-03 10:20:00', $rows[0]['estimated_departure']);
        $this->assertGaps($rows, [20]);
        $this->assertSame($history, $this->queue->find($departed));
        $migration->up();
        $this->assertSame($rows, $this->activeRows());
    }

    private function addRule(int $id, int $terminalId, ?int $routeId, int $minutes, string $updatedAt = '2026-10-01 10:00:00'): void
    {
        $this->intervalDb->table('departure_rules')->insert([
            'id' => $id, 'terminal_id' => $terminalId, 'route_id' => $routeId,
            'time_from' => '00:00:00', 'time_to' => '23:59:00',
            'wait_minutes' => $minutes, 'updated_at' => $updatedAt,
        ]);
    }

    private function addVehicle(int $routeId, int $position, ?string $departure = null): int
    {
        $this->intervalDb->table('queue')->insert([
            'vehicle_id' => $position, 'route_id' => $routeId, 'position' => $position,
            'status' => 'waiting', 'arrival_time' => '2026-10-03 10:00:00',
            'estimated_departure' => $departure,
        ]);
        return (int) $this->intervalDb->insertID();
    }

    private function activeRows(): array
    {
        return $this->queue->whereIn('status', ['waiting', 'boarding'])->orderBy('position', 'ASC')->findAll();
    }

    private function assertGaps(array $rows, array $minutes): void
    {
        $this->assertCount(count($minutes) + 1, $rows);
        foreach ($minutes as $index => $gap) {
            $this->assertSame($gap * 60,
                strtotime($rows[$index + 1]['estimated_departure']) - strtotime($rows[$index]['estimated_departure']));
        }
    }
}
