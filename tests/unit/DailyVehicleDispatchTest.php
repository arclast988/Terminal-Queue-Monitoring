<?php

namespace Tests\Unit;

use App\Models\QueueModel;
use App\Models\VehicleModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class DailyVehicleDispatchTest extends CIUnitTestCase
{
    private BaseConnection $dispatchDb;
    private VehicleModel $vehicles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dispatchDb = Database::connect(array_replace(config(Database::class)->tests, [
            'DBPrefix' => '', 'options' => '-c timezone=Asia/Manila -c application_name=tq_daily_dispatch_test',
        ]), false);
        $this->dispatchDb->query('CREATE TEMPORARY TABLE routes (id INTEGER PRIMARY KEY, terminal_id INTEGER, destination VARCHAR(100))');
        $this->dispatchDb->query('CREATE TEMPORARY TABLE vehicles (
            id SERIAL PRIMARY KEY, plate_number VARCHAR(20), type VARCHAR(50), status VARCHAR(20),
            default_route_id INTEGER, created_at TIMESTAMP, dispatch_order INTEGER DEFAULT 0,
            dispatch_rotation INTEGER DEFAULT 0, dispatch_rotation_date DATE
        )');
        $this->dispatchDb->query('CREATE TEMPORARY TABLE queue (
            id SERIAL PRIMARY KEY, vehicle_id INTEGER, route_id INTEGER, status VARCHAR(20), position INTEGER,
            arrival_time TIMESTAMP, departure_time TIMESTAMP, estimated_departure TIMESTAMP, boarding_start TIMESTAMP,
            current_passengers INTEGER DEFAULT 0, round_number INTEGER DEFAULT 1
        )');
        $this->dispatchDb->query('CREATE TEMPORARY TABLE dispatch_rounds (
            id SERIAL PRIMARY KEY, terminal_id INTEGER, destination VARCHAR(100), service_date DATE, round_number INTEGER
        )');
        $this->dispatchDb->table('routes')->insertBatch([
            ['id'=>1, 'terminal_id'=>1, 'destination'=>'ORMOC'],
            ['id'=>2, 'terminal_id'=>1, 'destination'=>'ORMOC'],
            ['id'=>3, 'terminal_id'=>1, 'destination'=>'BATO'],
            ['id'=>4, 'terminal_id'=>2, 'destination'=>'ORMOC'],
        ]);
        $this->vehicles = new VehicleModel($this->dispatchDb);
        foreach ([['A',1,1], ['B',2,2], ['C',1,3], ['D',3,1], ['E',4,1]] as [$plate,$route,$order]) {
            $this->vehicles->insert(['plate_number'=>$plate, 'type'=>'van', 'status'=>'active', 'default_route_id'=>$route, 'dispatch_order'=>$order]);
        }
    }

    protected function tearDown(): void
    {
        $this->dispatchDb->close();
        parent::tearDown();
    }

    public function testRegisterAndEditShiftPositionsAcrossTheDestinationWithoutDuplicates(): void
    {
        $this->assertTrue($this->vehicles->saveInDispatchOrder([
            'plate_number'=>'NEW', 'type'=>'jeepney', 'status'=>'active', 'default_route_id'=>2, 'dispatch_order'=>2,
        ]));
        $newId = (int) $this->vehicles->getInsertID();
        $this->assertSame(['A','NEW','B','C'], $this->platesForRoute([1,2]));
        $this->assertTrue($this->vehicles->saveInDispatchOrder(['dispatch_order'=>1], 3));
        $this->assertSame(['C','A','NEW','B'], $this->platesForRoute([1,2]));
        $this->assertSame([1,2,3,4], $this->positionsForRoute([1,2]));
        $this->assertSame(['D'], $this->platesForRoute([3]));
        $this->assertSame(['E'], $this->platesForRoute([4]));
        $this->assertGreaterThan(0, $newId);
    }

    public function testBlankOrderAppendsAndMovingToAnotherRouteCompactsBothLines(): void
    {
        $this->assertTrue($this->vehicles->saveInDispatchOrder(['default_route_id'=>3, 'dispatch_order'=>''], 2));
        $this->assertSame(['A','C'], $this->platesForRoute([1,2]));
        $this->assertSame([1,2], $this->positionsForRoute([1,2]));
        $this->assertSame(['D','B'], $this->platesForRoute([3]));
        $this->assertSame([1,2], $this->positionsForRoute([3]));
        $this->assertTrue($this->vehicles->saveInDispatchOrder(['dispatch_order'=>9999], 1));
        $this->assertSame(['C','A'], $this->platesForRoute([1,2]));
    }

    public function testEveryDepartureMovesTheVehicleToTheEndEvenWithinOneSecond(): void
    {
        $now = strtotime('2026-10-07 10:00:00');
        $this->assertSame(['A','B','C'], $this->dispatchPlates('2026-10-07'));
        foreach ([[1,1,['B','C','A']], [2,2,['C','A','B']], [3,1,['A','B','C']], [1,1,['B','C','A']]] as [$id,$route,$expected]) {
            $this->vehicles->rotateAfterDeparture($id, $route, $now);
            $this->assertSame($expected, $this->dispatchPlates('2026-10-07'));
        }
        $this->assertSame(['A','B','C'], $this->dispatchPlates('2026-10-08'));
        $this->assertSame([1,2,3], $this->positionsForRoute([1,2]));
        $this->assertSame(0, (int) $this->vehicles->find(4)['dispatch_rotation']);
        $this->assertSame(0, (int) $this->vehicles->find(5)['dispatch_rotation']);
    }

    public function testRouteReassignmentClearsTheOldRotationButSiblingVehicleTypesKeepIt(): void
    {
        $this->vehicles->rotateAfterDeparture(1, 1, strtotime('2026-10-07 10:00:00'));
        $this->assertTrue($this->vehicles->saveInDispatchOrder(['default_route_id'=>2, 'dispatch_order'=>1], 1));
        $this->assertSame(1, (int) $this->vehicles->find(1)['dispatch_rotation']);
        $this->assertTrue($this->vehicles->saveInDispatchOrder(['default_route_id'=>3, 'dispatch_order'=>1], 1));
        $this->assertSame(0, (int) $this->vehicles->find(1)['dispatch_rotation']);
        $this->assertNull($this->vehicles->find(1)['dispatch_rotation_date']);
        $this->vehicles->rotateAfterDeparture(1, 1, strtotime('2026-10-07 10:00:01'));
        $this->assertSame(0, (int) $this->vehicles->find(1)['dispatch_rotation']);
    }

    public function testMidnightCancelsOnlyOldActiveTripsAndResetsEachOldRoundOnce(): void
    {
        foreach (['waiting','boarding','departed','canceled','waiting','boarding'] as $index=>$status) {
            $this->dispatchDb->table('queue')->insert([
                'vehicle_id'=>$index+1, 'route_id'=>1, 'status'=>$status, 'position'=>$index+1,
                'arrival_time'=>$index<4 ? '2026-10-07 23:59:59' : '2026-10-08 00:00:00',
                'departure_time'=>$status==='departed' ? '2026-10-07 23:59:59' : null,
                'estimated_departure'=>'2026-10-08 00:10:00', 'boarding_start'=>'2026-10-07 23:50:00',
                'current_passengers'=>8,
            ]);
        }
        $this->dispatchDb->table('dispatch_rounds')->insertBatch([
            ['terminal_id'=>1,'destination'=>'ORMOC','service_date'=>'2026-10-07','round_number'=>3],
            ['terminal_id'=>1,'destination'=>'BATO','service_date'=>'2026-10-08','round_number'=>2],
        ]);
        $queue = new QueueModel($this->dispatchDb);
        $now = strtotime('2026-10-08 00:00:00');
        $reset = $queue->resetForNewDay($now);
        $this->assertEqualsCanonicalizing([1,2], $reset['canceled_ids']);
        $this->assertSame(1, $reset['rounds_reset']);
        foreach ([1,2] as $id) {
            $trip = $queue->find($id);
            $this->assertSame('canceled', $trip['status']);
            $this->assertSame(0, (int) $trip['position']);
            $this->assertNull($trip['estimated_departure']);
            $this->assertNull($trip['boarding_start']);
            $this->assertSame(8, (int) $trip['current_passengers']);
        }
        $this->assertSame('departed', $queue->find(3)['status']);
        $this->assertSame('2026-10-07 23:59:59', $queue->find(3)['departure_time']);
        $this->assertSame('canceled', $queue->find(4)['status']);
        $this->assertSame('waiting', $queue->find(5)['status']);
        $this->assertSame('boarding', $queue->find(6)['status']);
        $this->assertSame([1,2], array_map('intval', array_column($this->dispatchDb->table('dispatch_rounds')->orderBy('id')->get()->getResultArray(), 'round_number')));
        $this->assertSame(['canceled_ids'=>[], 'rounds_reset'=>0], $queue->resetForNewDay($now));
    }

    private function platesForRoute(array $ids): array
    {
        return array_column($this->vehicles->whereIn('default_route_id', $ids)->orderBy('dispatch_order')->findAll(), 'plate_number');
    }

    private function positionsForRoute(array $ids): array
    {
        return array_map('intval', array_column($this->vehicles->whereIn('default_route_id', $ids)->orderBy('dispatch_order')->findAll(), 'dispatch_order'));
    }

    private function dispatchPlates(string $today): array
    {
        $rows = $this->vehicles->select('vehicles.*, routes.terminal_id as route_terminal_id, routes.destination as route_destination')
            ->join('routes', 'routes.id = vehicles.default_route_id')->whereIn('default_route_id', [1,2])->findAll();
        return array_column(VehicleModel::sortForDispatch($rows, $today), 'plate_number');
    }
}
