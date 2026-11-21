<?php

namespace Tests\Unit;

use App\Controllers\Admin\Vehicles;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use Config\Database;

class VehicleStatusActionsHarness extends Vehicles
{
    public static array $broadcasts = [];
    protected function logActivity(string $action, string $details): void {}
    protected function broadcastUpdate(string $type, array $data = []): void { self::$broadcasts[] = [$type, $data]; }
}

final class VehicleStatusActionsTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    private BaseConnection $statusDb;
    private array $connections;

    protected function setUp(): void
    {
        parent::setUp();
        $this->statusDb = Database::connect(['DBDriver'=>'SQLite3', 'database'=>':memory:', 'DBPrefix'=>'', 'DBDebug'=>true], false);
        $property = new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances');
        $this->connections = $property->getValue();
        $property->setValue(null, array_replace($this->connections, ['default'=>$this->statusDb, 'tests'=>$this->statusDb]));
        foreach ([
            'CREATE TABLE terminals (id INTEGER PRIMARY KEY, name TEXT)',
            'CREATE TABLE routes (id INTEGER PRIMARY KEY, terminal_id INTEGER, destination TEXT, vehicle_type TEXT, status TEXT)',
            'CREATE TABLE vehicles (id INTEGER PRIMARY KEY, plate_number TEXT, driver_name TEXT, operator_name TEXT, owner_name TEXT, type TEXT, capacity INTEGER, status TEXT, default_route_id INTEGER, dispatch_order INTEGER, dispatch_rotation INTEGER DEFAULT 0, dispatch_rotation_date TEXT, photo TEXT, created_at TEXT)',
            'CREATE TABLE queue (id INTEGER PRIMARY KEY, vehicle_id INTEGER, route_id INTEGER, status TEXT, position INTEGER, arrival_time TEXT, departure_time TEXT, boarding_start TEXT, estimated_departure TEXT, round_number INTEGER DEFAULT 1, plate_number TEXT, operator_name TEXT, driver_name TEXT)',
            'CREATE TABLE departure_rules (id INTEGER PRIMARY KEY, terminal_id INTEGER, route_id INTEGER, time_from TEXT, time_to TEXT, wait_minutes INTEGER, days_of_week TEXT, day_of_week INTEGER, round_number INTEGER)',
            'CREATE TABLE vehicle_types (id INTEGER PRIMARY KEY, slug TEXT, is_active INTEGER)',
            'CREATE TABLE fare_discounts (id INTEGER PRIMARY KEY, type TEXT)',
            'CREATE TABLE fares (id INTEGER PRIMARY KEY, route_id INTEGER, fare_discount_id INTEGER, amount REAL)',
        ] as $sql) $this->statusDb->query($sql);
        $this->statusDb->table('terminals')->insert(['id'=>1,'name'=>'VILLABA']);
        $this->statusDb->table('routes')->insertBatch([
            ['id'=>1,'terminal_id'=>1,'destination'=>'ORMOC','vehicle_type'=>'van','status'=>'active'],
            ['id'=>2,'terminal_id'=>1,'destination'=>'BATO','vehicle_type'=>'van','status'=>'active'],
        ]);
        foreach (['active','active','maintenance','archived'] as $index=>$status) {
            $this->statusDb->table('vehicles')->insert([
                'id'=>$index+1,'plate_number'=>'VAN-'.($index+1),'driver_name'=>'Driver Name','operator_name'=>'OPERATOR NAME',
                'owner_name'=>'OPERATOR NAME','type'=>'van','capacity'=>18,'status'=>$status,'default_route_id'=>1,'dispatch_order'=>$index+1,
            ]);
        }
        foreach ([[1,1,1,'boarding',1],[2,2,1,'waiting',2],[3,1,1,'departed',0],[4,2,2,'waiting',1]] as [$id,$vehicle,$route,$status,$position]) {
            $this->statusDb->table('queue')->insert([
                'id'=>$id,'vehicle_id'=>$vehicle,'route_id'=>$route,'status'=>$status,'position'=>$position,
                'arrival_time'=>date('Y-m-d H:i:s'),'boarding_start'=>date('Y-m-d H:i:s'),'estimated_departure'=>date('Y-m-d H:i:s',time()+1200),
            ]);
        }
        $this->statusDb->table('vehicle_types')->insert(['id'=>1,'slug'=>'van','is_active'=>1]);
        $this->statusDb->table('fare_discounts')->insert(['id'=>1,'type'=>'regular']);
        $this->statusDb->table('fares')->insert(['id'=>1,'route_id'=>1,'fare_discount_id'=>1,'amount'=>150]);
        VehicleStatusActionsHarness::$broadcasts = [];
        session()->set(['role'=>'admin','isLoggedIn'=>true,'id'=>1]);
    }

    protected function tearDown(): void
    {
        (new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances'))->setValue(null, $this->connections);
        $this->statusDb->close();
        session()->remove(['errors','error','success','info','_ci_old_input']);
        parent::tearDown();
    }

    private function submit(int $id, array $post, string $method = 'updateStatus')
    {
        session()->remove(['errors','error','success','info','_ci_old_input']);
        service('validation')->reset();
        $this->withUri('http://localhost/admin/vehicles/status/'.$id);
        $this->request->setMethod('POST')->setGlobal('post',$post)->setGlobal('request',$post);
        $this->request->setHeader('Referer','http://localhost/admin/vehicles/edit/'.$id);
        return $this->controller(VehicleStatusActionsHarness::class)->execute($method,$id)->response();
    }

    private function row(string $table, int $id): array
    {
        return $this->statusDb->table($table)->where('id',$id)->get()->getRowArray();
    }

    public function testMaintenanceCancelsOnlyThisVehiclesActiveTripsAndCompactsItsLine(): void
    {
        $unrelated = $this->row('queue',4);
        $response = $this->submit(1,['status'=>'maintenance','current_status'=>'active']);
        $this->assertSame(302,$response->getStatusCode());
        $this->assertSame('maintenance',$this->row('vehicles',1)['status']);
        $this->assertSame('canceled',$this->row('queue',1)['status']);
        $this->assertSame(0,(int)$this->row('queue',1)['position']);
        $this->assertNull($this->row('queue',1)['estimated_departure']);
        $this->assertSame('departed',$this->row('queue',3)['status']);
        $this->assertSame(1,(int)$this->row('queue',2)['position']);
        $this->assertSame($unrelated,$this->row('queue',4));
        $this->assertSame(1,(int)$this->row('vehicles',2)['dispatch_order']);
        $this->assertStringContainsString('Maintenance',session()->getFlashdata('success'));
        $this->assertSame('maintenance',VehicleStatusActionsHarness::$broadcasts[0][1]['status']);
    }

    public function testSetActiveRestoresAvailabilityWithoutRestoringCanceledTrips(): void
    {
        $this->submit(1,['status'=>'maintenance','current_status'=>'active']);
        $this->submit(1,['status'=>'active','current_status'=>'maintenance']);
        $this->assertSame('active',$this->row('vehicles',1)['status']);
        $this->assertSame('canceled',$this->row('queue',1)['status']);
        $this->assertSame(2,(int)$this->row('vehicles',1)['dispatch_order']);
    }

    public function testInvalidArchivedAndStaleChangesCannotChangeAvailability(): void
    {
        foreach ([['status'=>'archived','current_status'=>'active'],['status'=>['maintenance'],'current_status'=>'active'],['status'=>'maintenance'],['status'=>'maintenance','current_status'=>'maintenance']] as $post) {
            $this->submit(1,$post);
            $this->assertSame('active',$this->row('vehicles',1)['status']);
            $this->assertNotEmpty(session()->getFlashdata('error'));
        }
        $this->submit(4,['status'=>'active','current_status'=>'maintenance']);
        $this->assertSame('archived',$this->row('vehicles',4)['status']);
        $this->assertSame([],VehicleStatusActionsHarness::$broadcasts);
    }

    public function testGetRequestsCannotChangeVehicleAvailability(): void
    {
        $this->withUri('http://localhost/admin/vehicles/status/1');
        $this->request->setMethod('GET')->setGlobal('post',['status'=>'maintenance','current_status'=>'active']);
        $response = $this->controller(VehicleStatusActionsHarness::class)->execute('updateStatus',1)->response();
        $this->assertSame(405,$response->getStatusCode());
        $this->assertSame('POST',$response->getHeaderLine('Allow'));
        $this->assertSame('active',$this->row('vehicles',1)['status']);
        $this->assertSame([],VehicleStatusActionsHarness::$broadcasts);
    }

    public function testTripCancellationFailureRollsBackVehicleStatusAndDispatchOrder(): void
    {
        $before = $this->statusDb->table('vehicles')->orderBy('id')->get()->getResultArray();
        $this->statusDb->query("CREATE TRIGGER fail_status_cancel BEFORE UPDATE ON queue WHEN NEW.status = 'canceled' BEGIN SELECT RAISE(ABORT, 'forced cancellation failure'); END");
        $this->submit(1,['status'=>'maintenance','current_status'=>'active']);
        $this->assertSame($before,$this->statusDb->table('vehicles')->orderBy('id')->get()->getResultArray());
        $this->assertSame('boarding',$this->row('queue',1)['status']);
        $this->assertNotEmpty(session()->getFlashdata('error'));
        $this->assertSame([],VehicleStatusActionsHarness::$broadcasts);
    }

    public function testEditingDetailsPreservesStatusAndIgnoresASpoofedStatusField(): void
    {
        foreach ([['id'=>1,'spoof'=>'maintenance'],['id'=>3,'spoof'=>'active'],['id'=>4,'spoof'=>'active']] as $case) {
            $id = $case['id'];
            $before = $this->row('vehicles',$id);
            $this->submit($id,[
                'plate_number'=>$before['plate_number'],'operator_name'=>'OPERATOR NAME','driver_name'=>'Driver Name','type'=>'van',
                'capacity'=>'22','route_id'=>'1','dispatch_order'=>(string)$before['dispatch_order'],'status'=>$case['spoof'],
            ],'update');
            $this->assertSame($before['status'],$this->row('vehicles',$id)['status']);
            $this->assertSame(22,(int)$this->row('vehicles',$id)['capacity']);
            $this->assertNotEmpty(session()->getFlashdata('success'));
        }
    }

    public function testEditingDetailsDoesNotRequireAStatusField(): void
    {
        $this->submit(1,['plate_number'=>'VAN-1','operator_name'=>'OPERATOR NAME','driver_name'=>'Updated Driver','type'=>'van','capacity'=>'18','route_id'=>'1','dispatch_order'=>'1'],'update');
        $this->assertSame('active',$this->row('vehicles',1)['status']);
        $this->assertSame('Updated Driver',$this->row('vehicles',1)['driver_name']);
        $this->assertNotEmpty(session()->getFlashdata('success'));
    }

    private function orderPost(string $plate = 'VAN-1', array $changes = []): array
    {
        return array_replace(['plate_number'=>$plate, 'operator_name'=>'OPERATOR NAME', 'driver_name'=>'Driver Name',
            'type'=>'van', 'capacity'=>'18', 'route_id'=>'1', 'dispatch_order'=>'1'], $changes);
    }

    public function testEditRejectsAThirdActivePositionWithoutChangingTheLineupOrRemovingItsPhoto(): void
    {
        $photo = 'uploads/vehicles/order-limit-test.png';
        $this->statusDb->table('vehicles')->where('id',1)->update(['photo'=>$photo]);
        $before = $this->statusDb->table('vehicles')->orderBy('id')->get()->getResultArray();
        $response = $this->submit(1,$this->orderPost('VAN-1',['dispatch_order'=>'3','remove_photo'=>'1']),'update');
        $this->assertSame(302,$response->getStatusCode());
        $this->assertStringContainsString('1 to 2',session()->getFlashdata('errors')['dispatch_order']);
        $this->assertSame($before,$this->statusDb->table('vehicles')->orderBy('id')->get()->getResultArray());
        $this->assertSame('3',session()->get('_ci_old_input')['post']['dispatch_order']);
        $this->assertSame([],VehicleStatusActionsHarness::$broadcasts);
    }

    public function testRegistrationAllowsTheNextSlotButRejectsPositionsBeyondIt(): void
    {
        $before = $this->statusDb->table('vehicles')->orderBy('id')->get()->getResultArray();
        $this->submit(0,$this->orderPost('NEW-1',['dispatch_order'=>'4']),'store');
        $this->assertStringContainsString('1 to 3',session()->getFlashdata('errors')['dispatch_order']);
        $this->assertSame($before,$this->statusDb->table('vehicles')->orderBy('id')->get()->getResultArray());
        $this->submit(0,$this->orderPost('NEW-1',['dispatch_order'=>'3']),'store');
        $saved = $this->statusDb->table('vehicles')->where('plate_number','NEW-1')->get()->getRowArray();
        $this->assertSame(3,(int)$saved['dispatch_order']);
        $this->assertNotEmpty(session()->getFlashdata('success'));
    }

    public function testAChangedDestinationUsesItsOwnCountAndBlankStillMeansLast(): void
    {
        $this->statusDb->table('fares')->insert(['id'=>2,'route_id'=>2,'fare_discount_id'=>1,'amount'=>100]);
        $before = $this->row('vehicles',1);
        $this->submit(1,$this->orderPost('VAN-1',['route_id'=>'2','dispatch_order'=>'2']),'update');
        $this->assertStringContainsString('1 to 1',session()->getFlashdata('errors')['dispatch_order']);
        $this->assertSame($before,$this->row('vehicles',1));
        $this->submit(1,$this->orderPost('VAN-1',['route_id'=>'2','dispatch_order'=>'']),'update');
        $this->assertSame(2,(int)$this->row('vehicles',1)['default_route_id']);
        $this->assertSame(1,(int)$this->row('vehicles',1)['dispatch_order']);
        $this->assertNotEmpty(session()->getFlashdata('success'));
    }

    public function testUnavailableVehiclesKeepOnlyTheirTrailingSlotsAndSiblingTypesShareTheActiveCount(): void
    {
        foreach ([3,4] as $id) {
            $this->submit($id,$this->orderPost('VAN-'.$id,['dispatch_order'=>'2']),'update');
            $this->assertStringContainsString('3 to 4',session()->getFlashdata('errors')['dispatch_order']);
        }
        $this->statusDb->table('routes')->insert(['id'=>3,'terminal_id'=>1,'destination'=>'ORMOC','vehicle_type'=>'van','status'=>'active']);
        $this->statusDb->table('vehicles')->where('id',2)->update(['default_route_id'=>3]);
        $this->submit(1,$this->orderPost('VAN-1',['dispatch_order'=>'2','capacity'=>'19']),'update');
        $this->assertSame(2,(int)$this->row('vehicles',1)['dispatch_order']);
        $this->assertNotEmpty(session()->getFlashdata('success'));
    }
}
