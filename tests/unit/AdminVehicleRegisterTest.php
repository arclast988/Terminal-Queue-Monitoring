<?php

namespace Tests\Unit;

use App\Controllers\Admin\Vehicles;
use App\Models\RouteModel;
use App\Models\VehicleModel;
use App\Models\VehicleTypeModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\View\View;
use Config\Database;
use Config\Services;

final class AdminVehicleRegisterTest extends CIUnitTestCase
{
    private BaseConnection $registerDb;
    private array $viewData = [];

    protected function setUp(): void
    {
        parent::setUp();
        // PostgreSQL in CI; temporary tables keep production and other tests isolated.
        $this->registerDb = Database::connect(array_replace(config(Database::class)->tests, [
            'DBPrefix' => '',
            // pg_connect reuses identical connection strings, even across CI objects.
            'options' => '-c timezone=Asia/Manila -c application_name=tq_vehicle_register_test',
        ]), false);
        $this->registerDb->query('CREATE TEMPORARY TABLE terminals (
            id INTEGER PRIMARY KEY, name VARCHAR(100)
        )');
        $this->registerDb->query('CREATE TEMPORARY TABLE routes (
            id INTEGER PRIMARY KEY, terminal_id INTEGER, destination VARCHAR(100),
            vehicle_type VARCHAR(50), status VARCHAR(20)
        )');
        $this->registerDb->query('CREATE TEMPORARY TABLE vehicles (
            id INTEGER PRIMARY KEY, plate_number VARCHAR(20), status VARCHAR(20),
            default_route_id INTEGER, created_at VARCHAR(30), type VARCHAR(50), dispatch_order INTEGER,
            dispatch_rotation INTEGER DEFAULT 0, dispatch_rotation_date DATE
        )');
    }

    protected function tearDown(): void
    {
        Services::resetSingle('renderer');
        $this->registerDb->close();
        parent::tearDown();
    }

    public function testRegisterRetainsVehiclesAndOnlyDisplaysActiveAssignments(): void
    {
        $this->registerDb->table('terminals')->insert(['id' => 1, 'name' => 'VILLABA']);
        $this->registerDb->table('routes')->insertBatch([
            ['id' => 1, 'terminal_id' => 1, 'destination' => 'ORMOC', 'vehicle_type' => 'van', 'status' => 'active'],
            ['id' => 2, 'terminal_id' => 1, 'destination' => 'PALOMPON', 'vehicle_type' => 'van', 'status' => 'archived'],
        ]);
        $this->registerDb->table('vehicles')->insertBatch([
            ['id' => 1, 'plate_number' => 'ACTIVE-1', 'status' => 'active', 'default_route_id' => 1, 'created_at' => '2026-10-05 08:00:00'],
            ['id' => 2, 'plate_number' => 'ARCHIVED-2', 'status' => 'active', 'default_route_id' => 2, 'created_at' => '2026-10-05 09:00:00'],
            ['id' => 3, 'plate_number' => 'MISSING-3', 'status' => 'active', 'default_route_id' => 999, 'created_at' => '2026-10-05 10:00:00'],
            ['id' => 4, 'plate_number' => 'UNASSIGNED-4', 'status' => 'archived', 'default_route_id' => null, 'created_at' => '2026-10-05 11:00:00'],
        ]);

        $result = $this->controller(new VehicleModel($this->registerDb))->index();
        $this->assertSame('Vehicle Register', $result);
        $rows = $this->viewData['vehicles'];
        $this->assertSame([1, 2, 3, 4], array_map('intval', array_column($rows, 'id')));
        $byId = array_column($rows, null, 'id');
        $this->assertSame(1, $byId[1]['dispatch_position']);
        $this->assertSame('VILLABA', $byId[1]['route_origin']);
        $this->assertSame('ORMOC', $byId[1]['route_destination']);
        $this->assertSame('van', $byId[1]['route_vehicle_type']);
        $this->assertSame('active', $byId[1]['route_status']);
        foreach ([2, 3, 4] as $id) {
            $this->assertNull($byId[$id]['dispatch_position']);
            $this->assertNull($byId[$id]['route_origin']);
            $this->assertNull($byId[$id]['route_destination']);
            $this->assertNull($byId[$id]['route_vehicle_type']);
            $this->assertNull($byId[$id]['route_status']);
        }
        $this->assertSame('archived', $byId[4]['status']);
    }

    public function testEmptyRegisterStillRenders(): void
    {
        $this->assertSame('Vehicle Register', $this->controller(new VehicleModel($this->registerDb))->index());
        $this->assertSame([], $this->viewData['vehicles']);
    }

    public function testControllerCompilesActiveStatusAsPostgresText(): void
    {
        // Compile with the production driver without needing a second database server.
        $postgres = Database::connect([
            'DBDriver' => 'Postgre', 'hostname' => '127.0.0.1',
            'database' => 'unused', 'username' => '', 'password' => '', 'DBPrefix' => '',
        ], false);
        $sql = '';
        $vehicles = $this->getMockBuilder(VehicleModel::class)
            ->setConstructorArgs([$postgres])->onlyMethods(['findAll'])->getMock();
        $vehicles->method('findAll')->willReturnCallback(static function () use ($vehicles, &$sql): array {
            $sql = $vehicles->builder()->getCompiledSelect();
            return [];
        });
        $this->controller($vehicles)->index();
        $this->assertStringContainsString('"routes"."status" = \'active\'', $sql);
        $this->assertStringNotContainsString('= "active"', $sql);
    }

    public function testEditShowsTheSameCurrentPositionAsTheRegisterAlongsideTheSavedStartingOrder(): void
    {
        $this->registerDb->table('terminals')->insert(['id'=>1, 'name'=>'VILLABA']);
        $this->registerDb->table('routes')->insert(['id'=>1, 'terminal_id'=>1, 'destination'=>'ORMOC', 'vehicle_type'=>'van', 'status'=>'active']);
        foreach (range(1,9) as $id) {
            $this->registerDb->table('vehicles')->insert(['id'=>$id, 'plate_number'=>'ORDER-'.$id, 'type'=>'van', 'status'=>'active', 'default_route_id'=>1,
                'dispatch_order'=>$id, 'dispatch_rotation'=>$id <= 6 ? $id : 0, 'dispatch_rotation_date'=>date('Y-m-d')]);
        }
        $this->assertSame('Edit Vehicle', $this->controller(new VehicleModel($this->registerDb), 'admin/vehicles/edit', 'Edit Vehicle')->edit(9));
        $this->assertSame(3, $this->viewData['vehicle']['dispatch_position']);
        $this->assertSame(9, (int) $this->viewData['vehicle']['dispatch_order']);
        $this->assertSame('ORMOC', $this->viewData['vehicle']['route_destination']);
    }

    private function controller(VehicleModel $vehicles, string $view = 'admin/vehicles/index', string $result = 'Vehicle Register'): Vehicles
    {
        $routes = $this->getMockBuilder(RouteModel::class)
            ->setConstructorArgs([$this->registerDb])->onlyMethods(['withActiveFare', 'findAll'])->getMock();
        $routes->method('withActiveFare')->willReturnSelf();
        $routes->method('findAll')->willReturn([]);
        $types = $this->getMockBuilder(VehicleTypeModel::class)
            ->setConstructorArgs([$this->registerDb])->onlyMethods(['findAll'])->getMock();
        $types->method('findAll')->willReturn([]);
        $renderer = $this->createMock(View::class);
        $renderer->method('setData')->willReturnCallback(function (array $data) use ($renderer): View {
            $this->viewData = $data;
            return $renderer;
        });
        $renderer->expects($this->once())->method('render')
            ->with($view)->willReturn($result);
        Services::injectMock('renderer', $renderer);

        return new class ($vehicles, $routes, $types) extends Vehicles {
            public function __construct(VehicleModel $vehicles, RouteModel $routes, VehicleTypeModel $types)
            {
                $this->vehicleModel = $vehicles;
                $this->routeModel = $routes;
                $this->vehicleTypeModel = $types;
            }
        };
    }
}
