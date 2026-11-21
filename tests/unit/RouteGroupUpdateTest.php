<?php

namespace Tests\Unit;

use App\Controllers\Admin\Routes;
use App\Models\RouteModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use Config\Database;

class RouteGroupUpdateHarness extends Routes
{
    public static array $broadcasts = [];
    protected function logActivity(string $action, string $details): void {}
    protected function broadcastUpdate(string $type, array $data = []): void { self::$broadcasts[] = [$type, $data]; }
}

final class RouteGroupUpdateTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    private BaseConnection $routeDb;
    private array $originalConfig;
    private array $originalConnections;
    private \ReflectionProperty $connections;

    protected function setUp(): void
    {
        parent::setUp();
        if (!class_exists(\SQLite3::class)) $this->markTestSkipped('SQLite3 is required for isolated route edits.');
        $this->originalConfig = config(Database::class)->tests;
        $this->connections = new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances');
        $this->originalConnections = $this->connections->getValue();
        $this->connections->setValue(null, []);
        config(Database::class)->tests = ['DBDriver'=>'SQLite3', 'database'=>':memory:', 'DBPrefix'=>'', 'DBDebug'=>true, 'foreignKeys'=>true];
        $this->routeDb = Database::connect();
        $this->routeDb->query('CREATE TABLE terminals (id INTEGER PRIMARY KEY, name TEXT)');
        $this->routeDb->query('CREATE TABLE routes (id INTEGER PRIMARY KEY AUTOINCREMENT, terminal_id INTEGER NOT NULL REFERENCES terminals(id), destination TEXT NOT NULL, vehicle_type TEXT NOT NULL, status TEXT, created_at TEXT, UNIQUE (terminal_id, destination, vehicle_type))');
        $this->routeDb->query('CREATE TABLE vehicle_types (id INTEGER PRIMARY KEY, slug TEXT, name TEXT, is_active INTEGER)');
        $this->routeDb->query('CREATE TABLE vehicles (id INTEGER PRIMARY KEY, default_route_id INTEGER REFERENCES routes(id) ON DELETE CASCADE, plate_number TEXT, type TEXT, status TEXT, dispatch_order INTEGER, dispatch_rotation INTEGER DEFAULT 0, dispatch_rotation_date TEXT)');
        $this->routeDb->query('CREATE TABLE user_routes (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, route_id INTEGER REFERENCES routes(id) ON DELETE CASCADE, UNIQUE(user_id,route_id))');
        $this->routeDb->query('CREATE TABLE fares (id INTEGER PRIMARY KEY, route_id INTEGER REFERENCES routes(id) ON DELETE CASCADE, amount REAL)');
        $this->routeDb->table('terminals')->insertBatch([['id'=>1,'name'=>'VILLABA'], ['id'=>2,'name'=>'PALOMPON']]);
        foreach (['minibus','van','jeepney'] as $index=>$type) {
            $this->routeDb->table('vehicle_types')->insert(['id'=>$index+1,'slug'=>$type,'name'=>ucfirst($type),'is_active'=>1]);
        }
        foreach ([[2,1,'ISABEL','van'], [3,1,'ISABEL','minibus'], [4,1,'ORMOC','van'], [5,2,'ISABEL','van']] as [$id,$terminal,$destination,$type]) {
            $this->routeDb->table('routes')->insert(['id'=>$id,'terminal_id'=>$terminal,'destination'=>$destination,'vehicle_type'=>$type,'status'=>'active']);
        }
        $this->routeDb->table('vehicles')->insert(['id'=>10,'default_route_id'=>2,'plate_number'=>'ABC-010','type'=>'van','status'=>'active','dispatch_order'=>1]);
        $this->routeDb->table('user_routes')->insert(['user_id'=>123,'route_id'=>2]);
        $this->routeDb->table('fares')->insert(['id'=>20,'route_id'=>2,'amount'=>100]);
        RouteGroupUpdateHarness::$broadcasts = [];
    }

    protected function tearDown(): void
    {
        if (isset($this->routeDb)) {
            $this->routeDb->close();
            config(Database::class)->tests = $this->originalConfig;
            $this->connections->setValue(null, $this->originalConnections);
        }
        session()->remove(['errors','error','success','info','_ci_old_input']);
        parent::tearDown();
    }

    private function submit(array $changes = []): void
    {
        session()->remove(['error','success','info','_ci_old_input']);
        $post = array_replace(['terminal_id'=>'1','destination'=>'ISABEL','vehicle_types'=>['van','minibus']], $changes);
        $this->withUri('http://localhost/admin/routes/update_group/2');
        $this->request->setMethod('POST')->setGlobal('post', $post)->setGlobal('request', $post);
        $this->request->setHeader('Referer', 'http://localhost/admin/routes/edit/2');
        $response = $this->controller(RouteGroupUpdateHarness::class)->execute('updateGroup', 2)->response();
        $this->assertSame(302, $response->getStatusCode());
    }

    private function snapshot(): array
    {
        $rows = [];
        foreach (['routes','vehicles','fares','user_routes'] as $table) {
            $rows[$table] = $this->routeDb->table($table)->orderBy('id')->get()->getResultArray();
        }
        return $rows;
    }

    public function testExistingDestinationReturnsAnExplanationAndPreservesEveryRecord(): void
    {
        $before = $this->snapshot();
        $this->submit(['destination'=>'ORMOC']);
        $this->assertStringContainsString('already exists', session()->getFlashdata('error'));
        $this->assertSame($before, $this->snapshot());
        $this->assertSame([], RouteGroupUpdateHarness::$broadcasts);
    }

    public function testMovingToExistingTerminalDestinationIsRejectedEvenForAnotherType(): void
    {
        $before = $this->snapshot();
        $this->submit(['terminal_id'=>'2']);
        $this->assertStringContainsString('ISABEL already exists at PALOMPON', session()->getFlashdata('error'));
        $this->assertSame($before, $this->snapshot());
    }

    public function testValidRenameKeepsRouteIdsAssignmentsAndFares(): void
    {
        $before = $this->snapshot();
        $this->submit(['destination'=>'  merida  ']);
        $this->assertNotNull(session()->getFlashdata('success'));
        $after = $this->snapshot();
        $this->assertSame(['MERIDA','MERIDA'], array_column(array_slice($after['routes'], 0, 2), 'destination'));
        foreach (['vehicles','fares','user_routes'] as $table) $this->assertSame($before[$table], $after[$table]);
        $this->assertSame([2,3,4,5], array_map('intval', array_column($after['routes'], 'id')));
        $this->assertCount(1, RouteGroupUpdateHarness::$broadcasts);
    }

    public function testAddingATypeAssignsExistingDispatcherAndPreservesFares(): void
    {
        $before = $this->snapshot();
        $this->submit(['vehicle_types'=>['van','minibus','jeepney']]);
        $this->assertNotNull(session()->getFlashdata('success'));
        $added = $this->routeDb->table('routes')->where('destination','ISABEL')->where('terminal_id',1)->where('vehicle_type','jeepney')->get()->getRowArray();
        $this->assertNotNull($added);
        $this->assertSame(1, $this->routeDb->table('user_routes')->where('user_id',123)->where('route_id',$added['id'])->countAllResults());
        $this->assertSame($before['fares'], $this->snapshot()['fares']);
    }

    public function testInvalidInputsReturnToTheFormWithoutWriting(): void
    {
        $before = $this->snapshot();
        foreach ([['terminal_id'=>'999'], ['terminal_id'=>['1']], ['destination'=>str_repeat('A',101)], ['destination'=>['ISABEL']], ['vehicle_types'=>[]], ['vehicle_types'=>['unknown']], ['vehicle_types'=>[['van']]]] as $changes) {
            $this->submit($changes);
            $this->assertNotNull(session()->getFlashdata('error'));
            $this->assertSame($before, $this->snapshot());
        }
    }

    public function testDatabaseFailureRollsBackAndReturnsAnErrorInsteadOfWhoops(): void
    {
        $this->routeDb->query("CREATE TRIGGER reject_new_variant BEFORE INSERT ON routes WHEN NEW.vehicle_type = 'jeepney' BEGIN SELECT RAISE(ABORT, 'simulated route write failure'); END");
        $before = $this->snapshot();
        // The rename succeeds first; the later insert must roll it all back.
        $this->submit(['destination'=>'MERIDA', 'vehicle_types'=>['van','minibus','jeepney']]);
        $this->assertStringContainsString('original routes were kept', session()->getFlashdata('error'));
        $this->assertSame($before, $this->snapshot());
        $this->assertSame([], RouteGroupUpdateHarness::$broadcasts);
    }

    private function submitAction(string $method, array $post, ?int $id = 2): void
    {
        session()->remove(['errors','error','success','info','_ci_old_input']);
        service('validation')->reset();
        $this->withUri('http://localhost/admin/routes/' . $method . ($id ? '/' . $id : ''));
        $this->request->setMethod('POST')->setGlobal('post', $post)->setGlobal('request', $post);
        $this->request->setHeader('Referer', 'http://localhost/admin/routes/edit/2');
        $controller = $this->controller(RouteGroupUpdateHarness::class);
        $response = $id === null ? $controller->execute($method)->response() : $controller->execute($method, $id)->response();
        $this->assertSame(302, $response->getStatusCode());
    }

    public function testLocationComparisonIgnoresCaseAndUnicodeSpacingWithoutMergingOtherPlaces(): void
    {
        $this->assertTrue(RouteModel::sameLocation("\tOrmoc\u{00A0}", ' ORMOC '));
        $this->assertTrue(RouteModel::sameLocation(" Puerto\u{00A0}\u{2003}Princesa ", "puerto\tprincesa"));
        $this->assertFalse(RouteModel::sameLocation('ORMOC', 'ISABEL'));
        $this->assertFalse(RouteModel::sameLocation('ORMOC', 'ORMOC EAST'));
        $this->assertFalse(RouteModel::sameLocation('', ''));
    }

    public function testCreateGroupLegacyEditAndFareFormsRejectSameOriginWithoutWriting(): void
    {
        $this->routeDb->table('terminals')->where('id', 1)->update(['name'=>" OrMoC\u{00A0} "]);
        $before = $this->snapshot();
        foreach ([
            ['store', ['form_action'=>'create_route','terminal_id'=>'1','destination'=>"\tormoc ",'vehicle_types'=>['van']], null],
            ['updateGroup', ['terminal_id'=>'1','destination'=>' oRmOc ','vehicle_types'=>['van','minibus']], 2],
            ['update', ['terminal_id'=>'1','destination'=>' ormoc ','vehicle_type'=>'van','fare'=>'125.00'], 2],
            ['store', ['form_action'=>'assign_fare','terminal_id'=>'1','destination'=>' ormoc ','vehicle_types'=>['van'],'fare'=>'125.00'], null],
            ['update', ['form_action'=>'update_fares','terminal_id'=>'1','destination'=>' ormoc ','vehicle_types'=>['van'],'fare'=>'125.00'], 2],
        ] as [$method, $post, $id]) {
            $this->submitAction($method, $post, $id);
            $this->assertSame('Choose a destination different from the origin terminal.', session()->getFlashdata('errors')['destination']);
            $this->assertSame($before, $this->snapshot());
            $this->assertSame([], RouteGroupUpdateHarness::$broadcasts);
        }
    }

    public function testCreatingADifferentDestinationPreservesExistingRoutesAndCreatesSelectedVariants(): void
    {
        $before = $this->snapshot();
        $this->submitAction('store', ['form_action'=>'create_route','terminal_id'=>'1','destination'=>'MERIDA','vehicle_types'=>['van','minibus']], null);
        $this->assertNotNull(session()->getFlashdata('success'));
        $after = $this->snapshot();
        $this->assertSame($before['routes'], array_slice($after['routes'], 0, count($before['routes'])));
        $created = array_slice($after['routes'], count($before['routes']));
        $this->assertSame(['MERIDA','MERIDA'], array_column($created,'destination'));
        $this->assertSame(['van','minibus'], array_column($created,'vehicle_type'));
        foreach (['vehicles','fares','user_routes'] as $table) $this->assertSame($before[$table], $after[$table]);
    }

    public function testLegacySelfRouteCannotBeActivatedIndividuallyOrInABulkSelection(): void
    {
        $this->routeDb->table('routes')->whereIn('id',[2,3])->update(['status'=>'archived']);
        $this->routeDb->table('routes')->where('id',4)->update(['destination'=>' villaba ', 'status'=>'archived']);
        $before = $this->snapshot();
        $this->submitAction('activateGroup', [], 4);
        $this->assertStringContainsString('origin terminal', session()->getFlashdata('error'));
        $this->assertSame($before, $this->snapshot());
        $this->submitAction('bulkAction', ['action'=>'activate','ids'=>['2','4']], null);
        $this->assertStringContainsString('origin terminal', session()->getFlashdata('error'));
        $this->assertSame($before, $this->snapshot());
        $this->assertSame([], RouteGroupUpdateHarness::$broadcasts);
    }

    public function testValidArchivedRoutesStillActivateNormally(): void
    {
        $this->routeDb->table('routes')->whereIn('id',[2,3])->update(['status'=>'archived']);
        $this->submitAction('activateGroup', [], 2);
        $this->assertNotNull(session()->getFlashdata('success'));
        $this->assertSame(['active','active'], array_column($this->routeDb->table('routes')->whereIn('id',[2,3])->get()->getResultArray(),'status'));
        $this->routeDb->table('routes')->whereIn('id',[2,3,5])->update(['status'=>'archived']);
        $this->submitAction('bulkAction', ['action'=>'activate','ids'=>['2','5']], null);
        $this->assertNotNull(session()->getFlashdata('success'));
        $this->assertSame(['active','active','active'], array_column($this->routeDb->table('routes')->whereIn('id',[2,3,5])->get()->getResultArray(),'status'));
    }
}
