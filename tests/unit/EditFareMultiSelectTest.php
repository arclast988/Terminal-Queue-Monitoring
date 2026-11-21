<?php

namespace Tests\Unit;

use App\Controllers\Admin\Routes;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use Config\Database;

class EditFareMultiSelectHarness extends Routes
{
    protected function logActivity(string $action, string $details): void {}
    protected function broadcastUpdate(string $type, array $data = []): void {}
}

final class EditFareMultiSelectTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    private BaseConnection $fareDb;
    private array $originalConfig;
    private array $originalConnections;
    private \ReflectionProperty $connections;

    protected function setUp(): void
    {
        parent::setUp();
        if (!class_exists(\SQLite3::class)) $this->markTestSkipped('SQLite3 is required for isolated fare transactions.');
        $this->originalConfig = config(Database::class)->tests;
        $this->connections = new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances');
        $this->originalConnections = $this->connections->getValue();
        $this->connections->setValue(null, []);
        config(Database::class)->tests = ['DBDriver'=>'SQLite3', 'database'=>':memory:', 'DBPrefix'=>'', 'DBDebug'=>true];
        $this->fareDb = Database::connect();
        $this->fareDb->query('CREATE TABLE terminals (id INTEGER PRIMARY KEY, name TEXT)');
        $this->fareDb->query('CREATE TABLE routes (id INTEGER PRIMARY KEY, terminal_id INTEGER, destination TEXT, vehicle_type TEXT, status TEXT, created_at TEXT)');
        $this->fareDb->query('CREATE TABLE vehicle_types (id INTEGER PRIMARY KEY, slug TEXT, name TEXT, is_active INTEGER)');
        $this->fareDb->query('CREATE TABLE fare_discounts (id INTEGER PRIMARY KEY AUTOINCREMENT, terminal_id INTEGER, type TEXT, label TEXT, discount_percent REAL, is_active INTEGER, created_at TEXT, updated_at TEXT)');
        $this->fareDb->query('CREATE TABLE fares (id INTEGER PRIMARY KEY AUTOINCREMENT, route_id INTEGER, fare_discount_id INTEGER, amount REAL, created_at TEXT, updated_at TEXT)');
        $this->fareDb->table('terminals')->insertBatch([['id'=>1,'name'=>'VILLABA'], ['id'=>2,'name'=>'PALOMPON']]);
        foreach (['minibus','van','jeepney','bus'] as $index=>$type) {
            $this->fareDb->table('vehicle_types')->insert(['id'=>$index+1,'slug'=>$type,'name'=>ucfirst($type),'is_active'=>1]);
        }
        foreach ([[10,1,'ISABEL','minibus'],[11,1,'ISABEL','van'],[12,1,'ISABEL','jeepney'],[13,2,'ISABEL','minibus'],[14,1,'ORMOC','van']] as [$id,$terminal,$destination,$type]) {
            $this->fareDb->table('routes')->insert(['id'=>$id,'terminal_id'=>$terminal,'destination'=>$destination,'vehicle_type'=>$type,'status'=>'active']);
        }
        $this->fareDb->table('fare_discounts')->insertBatch([
            ['id'=>1,'terminal_id'=>1,'type'=>'regular','label'=>'Regular','discount_percent'=>0,'is_active'=>1],
            ['id'=>2,'terminal_id'=>1,'type'=>'student','label'=>'Student','discount_percent'=>20,'is_active'=>1],
            ['id'=>3,'terminal_id'=>2,'type'=>'regular','label'=>'Regular','discount_percent'=>0,'is_active'=>1],
        ]);
        foreach ([[10,1,100],[10,2,80],[12,1,88],[13,3,99],[14,1,123]] as [$route,$discount,$amount]) {
            $this->fareDb->table('fares')->insert(['route_id'=>$route,'fare_discount_id'=>$discount,'amount'=>$amount]);
        }
    }

    protected function tearDown(): void
    {
        if (isset($this->fareDb)) {
            $this->fareDb->close();
            config(Database::class)->tests = $this->originalConfig;
            $this->connections->setValue(null, $this->originalConnections);
        }
        session()->remove(['errors','error','success','info','_ci_old_input']);
        parent::tearDown();
    }

    private function submit(array $types, array $extra = []): void
    {
        session()->remove(['errors','error','success','info','_ci_old_input']);
        service('validation')->reset();
        $post = $extra + ['form_action'=>'update_fares','edit_source_id'=>'10','terminal_id'=>'1','destination'=>'ISABEL','fare'=>'155.00'];
        if ($types) $post['vehicle_types'] = $types;
        $this->withUri('http://localhost/admin/routes/update/10');
        $this->request->setMethod('POST')->setGlobal('post', $post)->setGlobal('request', $post);
        $this->request->setHeader('Referer', 'http://localhost/fares');
        $response = $this->controller(EditFareMultiSelectHarness::class)->execute('update', 10)->response();
        $this->assertSame(302, $response->getStatusCode());
    }

    private function amount(int $route, int $discount = 1): float
    {
        return (float) $this->fareDb->table('fares')->where('route_id',$route)->where('fare_discount_id',$discount)->get()->getRowArray()['amount'];
    }

    private function assign(array $extra): void
    {
        session()->remove(['errors','error','success','info','_ci_old_input']);
        service('validation')->reset();
        $post = $extra + ['form_action'=>'assign_fare','terminal_id'=>'2','destination'=>'ISABEL','fare'=>'155.50','vehicle_types'=>['minibus']];
        $this->withUri('http://localhost/admin/routes/store');
        $this->request->setMethod('POST')->setGlobal('post', $post)->setGlobal('request', $post);
        $this->request->setHeader('Referer', 'http://localhost/fares');
        $response = $this->controller(EditFareMultiSelectHarness::class)->execute('store')->response();
        $this->assertSame(302, $response->getStatusCode());
    }

    public function testAssigningASharedDestinationUsesOnlyTheSelectedTerminal(): void
    {
        $this->fareDb->table('fares')->where('route_id',13)->delete();
        $this->fareDb->table('fare_discounts')->insert(['id'=>4,'terminal_id'=>2,'type'=>'student','label'=>'Student','discount_percent'=>15,'is_active'=>1]);
        $before = $this->fareDb->table('fares')->where('route_id !=',13)->orderBy('id')->get()->getResultArray();
        $this->assign([]);
        $this->assertNotNull(session()->getFlashdata('success'));
        $this->assertSame(155.5,$this->amount(13,3));
        $this->assertSame(132.18,$this->amount(13,4));
        $this->assertSame($before,$this->fareDb->table('fares')->where('route_id !=',13)->orderBy('id')->get()->getResultArray());
    }

    public function testMissingDestinationKeepsTheSubmittedFareTerminalAndTypesWithoutWriting(): void
    {
        $before = $this->fareDb->table('fares')->orderBy('id')->get()->getResultArray();
        $this->assign(['destination'=>'']);
        $this->assertSame('Select the route destination for this fare.',session()->getFlashdata('errors')['destination']);
        $post = session()->get('_ci_old_input')['post'];
        $this->assertSame('assign_fare',$post['form_action']);
        $this->assertSame('2',$post['terminal_id']);
        $this->assertSame('155.50',$post['fare']);
        $this->assertSame(['minibus'],$post['vehicle_types']);
        $this->assertSame($before,$this->fareDb->table('fares')->orderBy('id')->get()->getResultArray());
    }

    public function testSelectedTypesReceiveTheFareAndDiscountsWithoutChangingOtherRoutes(): void
    {
        $routesBefore = $this->fareDb->table('routes')->orderBy('id')->get()->getResultArray();
        $this->submit(['minibus','van','van']);
        foreach ([10,11] as $id) {
            $this->assertSame(155.0, $this->amount($id));
            $this->assertSame(124.0, $this->amount($id,2));
            $this->assertSame(2, $this->fareDb->table('fares')->where('route_id',$id)->countAllResults());
        }
        $this->assertSame(88.0,$this->amount(12));
        $this->assertSame(99.0,$this->amount(13,3));
        $this->assertSame(123.0,$this->amount(14));
        $this->assertSame($routesBefore, $this->fareDb->table('routes')->orderBy('id')->get()->getResultArray());
        $this->assertNotNull(session()->getFlashdata('success'));
    }

    public function testDeselectingTheSourceTypePreservesItsFare(): void
    {
        $this->submit(['van','jeepney']);
        $this->assertSame(100.0,$this->amount(10));
        $this->assertSame(80.0,$this->amount(10,2));
        $this->assertSame(155.0,$this->amount(11));
        $this->assertSame(155.0,$this->amount(12));
        $this->assertSame('minibus',$this->fareDb->table('routes')->where('id',10)->get()->getRowArray()['vehicle_type']);
    }

    public function testUnregisteredTypeRejectsTheWholeSelectionWithoutWriting(): void
    {
        $before = $this->fareDb->table('fares')->orderBy('id')->get()->getResultArray();
        $this->submit(['minibus','bus']);
        $this->assertStringContainsString('Create or restore that route in Route Management first.',session()->getFlashdata('errors')['vehicle_types']);
        $this->assertSame($before,$this->fareDb->table('fares')->orderBy('id')->get()->getResultArray());
    }

    public function testMissingSelectionShowsAnActionableMessage(): void
    {
        $this->submit([]);
        $this->assertSame('Select at least one vehicle type to update its fare.',session()->getFlashdata('errors')['vehicle_types']);
    }

    public function testFailureOnTheSecondTypeRollsBackTheFirstType(): void
    {
        $this->assertRollbackOnRoute(11);
    }

    public function testFailureOnTheFirstTypeStopsBeforeUpdatingAnotherType(): void
    {
        $this->assertRollbackOnRoute(10);
    }

    private function assertRollbackOnRoute(int $routeId): void
    {
        $before = $this->fareDb->table('fares')->orderBy('id')->get()->getResultArray();
        $this->fareDb->query("CREATE TRIGGER fail_selected_fare BEFORE INSERT ON fares WHEN NEW.route_id = $routeId BEGIN SELECT RAISE(ABORT, 'fixture failure'); END");
        $this->submit(['minibus','van']);
        $this->assertSame('The fares could not be updated. Please try again.',session()->getFlashdata('error'));
        $this->assertSame($before,$this->fareDb->table('fares')->orderBy('id')->get()->getResultArray());
    }
}
