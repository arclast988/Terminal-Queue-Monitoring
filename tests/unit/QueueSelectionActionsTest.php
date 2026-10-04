<?php

namespace Tests\Unit;

use App\Controllers\Staff\Queue;
use App\Models\QueueModel;
use App\Models\RouteModel;
use App\Models\VehicleModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use Config\Database;

class QueueSelectionHarness extends Queue
{
    public static $db;
    public function __construct()
    {
        $this->queueModel = new QueueModel(self::$db);
        $this->routeModel = new RouteModel(self::$db);
        $this->vehicleModel = new VehicleModel(self::$db);
    }
    protected function getAssignedRouteIds(): ?array { return [1, 2]; }
    protected function logActivity(string $action, string $details): void {}
    protected function broadcastUpdate(string $type, array $data = []): void {}
}

final class QueueSelectionActionsTest extends CIUnitTestCase
{
    use ControllerTestTrait;
    private array $connections;
    protected function setUp(): void
    {
        parent::setUp();
        QueueSelectionHarness::$db = Database::connect(['DBDriver'=>'SQLite3', 'database'=>':memory:', 'DBPrefix'=>'', 'DBDebug'=>true], false);
        $property = new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances');
        $this->connections = $property->getValue();
        $property->setValue(null, array_replace($this->connections, ['default'=>QueueSelectionHarness::$db, 'tests'=>QueueSelectionHarness::$db]));
        $db = QueueSelectionHarness::$db;
        $db->query('CREATE TABLE terminals (id INTEGER PRIMARY KEY, name TEXT, capacity INTEGER)');
        $db->query('CREATE TABLE routes (id INTEGER PRIMARY KEY, terminal_id INTEGER, destination TEXT, status TEXT)');
        $db->query('CREATE TABLE vehicles (id INTEGER PRIMARY KEY, plate_number TEXT, driver_name TEXT, operator_name TEXT, owner_name TEXT, type TEXT, photo TEXT, capacity INTEGER)');
        $db->query('CREATE TABLE departure_rules (id INTEGER PRIMARY KEY, terminal_id INTEGER, route_id INTEGER, time_from TEXT, time_to TEXT, wait_minutes INTEGER, day_of_week INTEGER, days_of_week TEXT, round_number INTEGER)');
        $db->query('CREATE TABLE queue (id INTEGER PRIMARY KEY, vehicle_id INTEGER, route_id INTEGER, status TEXT, position INTEGER, arrival_time TEXT, estimated_departure TEXT, departure_time TEXT, boarding_start TEXT, round_number INTEGER, current_passengers INTEGER, plate_number TEXT, operator_name TEXT, driver_name TEXT)');
        $db->table('terminals')->insert(['id'=>1,'name'=>'VILLABA','capacity'=>50]);
        foreach ([1=>'ORMOC', 2=>'TACLOBAN', 3=>'OTHER'] as $id=>$destination) $db->table('routes')->insert(['id'=>$id,'terminal_id'=>1,'destination'=>$destination,'status'=>'active']);
        for ($id=1; $id<=4; $id++) {
            $db->table('vehicles')->insert(['id'=>$id,'plate_number'=>'ABC-'.$id,'type'=>'minibus','capacity'=>20]);
            $db->table('queue')->insert(['id'=>$id,'vehicle_id'=>$id,'route_id'=>$id===4?3:($id===3?2:1),'status'=>$id===1?'boarding':'waiting','position'=>$id===2?2:1,'arrival_time'=>date('Y-m-d H:i:s'),'estimated_departure'=>date('Y-m-d H:i:s',time()+1800),'boarding_start'=>date('Y-m-d H:i:s'),'round_number'=>1]);
        }
        session()->set(['role'=>'staff','isLoggedIn'=>true,'id'=>123]);
    }
    protected function tearDown(): void
    {
        (new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances'))->setValue(null, $this->connections);
        QueueSelectionHarness::$db->close();
        parent::tearDown();
    }
    private function cancel(array $ids)
    {
        return $this->withBody(json_encode(['queue_ids'=>$ids]))->withResponse(service('response',null,false))->withRequest(service('request')->setMethod('POST')->setHeader('X-Requested-With','XMLHttpRequest')->setHeader('Content-Type','application/json'))
            ->controller(QueueSelectionHarness::class)->execute('cancelSelected')->response();
    }
    public function testSelectedCancellationKeepsUnselectedTripsAndRestorePositions(): void
    {
        $response=$this->cancel([1,3]);
        $this->assertSame(200,$response->getStatusCode());
        $db=QueueSelectionHarness::$db;
        foreach ([1,3] as $id) {
            $row=$db->table('queue')->where('id',$id)->get()->getRowArray();
            $this->assertSame('canceled',$row['status']);
            $this->assertSame(1,(int)$row['position']);
            $this->assertNull($row['estimated_departure']);
            $this->assertNull($row['boarding_start']);
        }
        $this->assertSame('waiting',$db->table('queue')->where('id',2)->get()->getRowArray()['status']);
        $this->assertSame(1,(int)$db->table('queue')->where('id',2)->get()->getRowArray()['position']);
        $this->assertSame('waiting',$db->table('queue')->where('id',4)->get()->getRowArray()['status']);
        $this->assertSame(200,$this->cancel([1,3])->getStatusCode());
    }
    public function testUnauthorizedTripRejectsTheWholeSelection(): void
    {
        $this->assertSame(403,$this->cancel([1,4])->getStatusCode());
        $this->assertSame('boarding',QueueSelectionHarness::$db->table('queue')->where('id',1)->get()->getRowArray()['status']);
    }
    public function testStaleSelectionRejectsEveryTripWithoutPartialCancellation(): void
    {
        QueueSelectionHarness::$db->table('queue')->where('id',2)->update(['status'=>'departed']);
        $this->assertSame(409,$this->cancel([1,2])->getStatusCode());
        $this->assertSame('boarding',QueueSelectionHarness::$db->table('queue')->where('id',1)->get()->getRowArray()['status']);
        $this->assertSame(409,$this->cancel([1,999])->getStatusCode());
    }
    public function testInvalidAndEmptySelectionsAreRejected(): void
    {
        foreach ([[],[0],['1 OR 1=1'],[[1]]] as $ids) $this->assertSame(400,$this->cancel($ids)->getStatusCode());
    }
    public function testFormEncodedSelectionUsedByTheBrowserIsAccepted(): void
    {
        $this->request->setMethod('POST')->setHeader('Content-Type','application/x-www-form-urlencoded')->setGlobal('post',['queue_ids'=>['2']]);
        $result=$this->withBody('queue_ids%5B%5D=2')->controller(QueueSelectionHarness::class)->execute('cancelSelected');
        $this->assertSame(200,$result->response()->getStatusCode());
        $this->assertSame('canceled',QueueSelectionHarness::$db->table('queue')->where('id',2)->get()->getRowArray()['status']);
    }
    public function testRepeatedSingleCancelReturnsSuccessAndDoesNotRestartOtherTimers(): void
    {
        $request=service('request')->setMethod('POST')->setHeader('X-Requested-With','XMLHttpRequest')->setBody('');
        $response=$this->withRequest($request)->controller(QueueSelectionHarness::class)->execute('updateStatus',1,'canceled')->response();
        $this->assertSame(200,$response->getStatusCode());
        $after=QueueSelectionHarness::$db->table('queue')->where('id',2)->get()->getRowArray();
        $response=$this->withRequest($request)->controller(QueueSelectionHarness::class)->execute('updateStatus',1,'canceled')->response();
        $this->assertSame(200,$response->getStatusCode());
        $this->assertSame($after,QueueSelectionHarness::$db->table('queue')->where('id',2)->get()->getRowArray());
    }
    public function testManualBoardingStartsImmediatelyAndRetainsTheRuleInterval(): void
    {
        QueueSelectionHarness::$db->table('queue')->where('id',1)->update(['status'=>'waiting','boarding_start'=>date('Y-m-d').' 23:55:00']);
        $request=service('request')->setMethod('POST')->setHeader('X-Requested-With','XMLHttpRequest')->setBody('');
        $before=time();
        $response=$this->withRequest($request)->controller(QueueSelectionHarness::class)->execute('updateStatus',1,'boarding')->response();
        $data=json_decode($response->getBody(),true);
        $this->assertSame(200,$response->getStatusCode());
        $this->assertTrue($data['success']);
        $row=QueueSelectionHarness::$db->table('queue')->where('id',1)->get()->getRowArray();
        $this->assertSame('boarding',$row['status']);
        $this->assertGreaterThanOrEqual($before,strtotime($row['boarding_start']));
        $this->assertLessThanOrEqual(time(),strtotime($row['boarding_start']));
        $this->assertSame(1800,strtotime($row['estimated_departure'])-strtotime($row['boarding_start']));
        $this->withRequest($request)->controller(QueueSelectionHarness::class)->execute('updateStatus',1,'boarding');
        $this->assertSame($row,QueueSelectionHarness::$db->table('queue')->where('id',1)->get()->getRowArray());
    }
    public function testManualBoardingCannotSkipTheVehicleAhead(): void
    {
        $request=service('request')->setMethod('POST')->setHeader('X-Requested-With','XMLHttpRequest')->setBody('');
        $response=$this->withRequest($request)->controller(QueueSelectionHarness::class)->execute('updateStatus',2,'boarding')->response();
        $this->assertSame(409,$response->getStatusCode());
        $data=json_decode($response->getBody(),true);
        $this->assertSame('warning',$data['variant']);
        $this->assertStringContainsString('vehicle is ahead',$data['message']);
    }
    public function testClockParsingAndDisplayRespectTheRole(): void
    {
        $this->assertSame('00:00:00',departure_clock_value('12:00 AM',true));
        $this->assertSame('12:00:00',departure_clock_value('12:00 PM',true));
        $this->assertSame('23:59:00',departure_clock_value('11:59 pm',true));
        foreach (['13:00 PM','00:30 AM','7:60 PM','7:00','7:00 P'] as $invalid) $this->assertNull(departure_clock_value($invalid,true));
        $this->assertNull(departure_clock_value('5:00 PM',false));
        $this->assertSame('17:00:00',departure_clock_value('17:00',false));
        $this->assertSame('5:00 PM',operations_time('17:00'));
        foreach (['admin','super_admin'] as $role) { session()->set('role',$role); $this->assertSame('17:00',operations_time('17:00')); }
    }
}
