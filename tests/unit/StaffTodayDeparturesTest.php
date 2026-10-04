<?php

namespace Tests\Unit;

use App\Controllers\Staff\Departures;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use Config\Database;

final class StaffTodayDeparturesTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    private BaseConnection $departureDb;
    private array $connections;
    private string $today;

    protected function setUp(): void
    {
        parent::setUp();
        service('renderer')->resetData();
        $this->today = date('Y-m-d');
        $this->departureDb = Database::connect(['DBDriver'=>'SQLite3', 'database'=>':memory:', 'DBPrefix'=>'', 'DBDebug'=>true], false);
        $property = new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances');
        $this->connections = $property->getValue();
        $property->setValue(null, array_replace($this->connections, ['default'=>$this->departureDb, 'tests'=>$this->departureDb]));
        $db = $this->departureDb;
        $db->query('CREATE TABLE terminals (id INTEGER PRIMARY KEY, name TEXT)');
        $db->query('CREATE TABLE routes (id INTEGER PRIMARY KEY, terminal_id INTEGER, destination TEXT)');
        $db->query('CREATE TABLE user_routes (id INTEGER PRIMARY KEY, user_id INTEGER, route_id INTEGER)');
        $db->query('CREATE TABLE vehicles (id INTEGER PRIMARY KEY, plate_number TEXT, driver_name TEXT, operator_name TEXT, owner_name TEXT, type TEXT)');
        $db->query('CREATE TABLE queue (id INTEGER PRIMARY KEY, vehicle_id INTEGER, route_id INTEGER, status TEXT, departure_time TEXT, plate_number TEXT, driver_name TEXT, operator_name TEXT, current_passengers INTEGER)');
        $db->table('terminals')->insertBatch([['id'=>1,'name'=>'VILLABA'],['id'=>2,'name'=>'OTHER TERMINAL']]);
        $db->table('routes')->insertBatch([
            ['id'=>1,'terminal_id'=>1,'destination'=>'ORMOC'], ['id'=>2,'terminal_id'=>1,'destination'=>'ORMOC'],
            ['id'=>3,'terminal_id'=>1,'destination'=>'TACLOBAN'], ['id'=>4,'terminal_id'=>2,'destination'=>'ORMOC'],
        ]);
        $db->table('user_routes')->insert(['id'=>1,'user_id'=>123,'route_id'=>1]);
        $db->table('vehicles')->insert(['id'=>1,'plate_number'=>'LIVE-PLATE','driver_name'=>'Live Driver','operator_name'=>'Live Operator','owner_name'=>'Owner','type'=>'van']);
        for ($id=1; $id<=35; $id++) $this->addTrip($id, sprintf('TODAY-%02d',$id), 1, $this->today.' 10:00:00', 10);
        $this->addTrip(100, 'ASSIGNED-SIBLING', 2, $this->today.' 10:05:00', 5);
        $this->addTrip(101, 'UNASSIGNED-ROUTE', 3, $this->today.' 10:10:00');
        $this->addTrip(102, 'UNASSIGNED-TERMINAL', 4, $this->today.' 10:15:00');
        $this->addTrip(103, 'YESTERDAY-TRIP', 1, date('Y-m-d',strtotime($this->today.' -1 day')).' 12:00:00');
        $this->addTrip(104, 'TOMORROW-TRIP', 1, date('Y-m-d',strtotime($this->today.' +1 day')).' 12:00:00');
        $this->addTrip(105, 'WAITING-TRIP', 1, $this->today.' 10:00:00');
        $db->table('queue')->where('id',105)->update(['status'=>'waiting']);
        $this->addTrip(106, 'MIDNIGHT-ROLLOVER', 1, date('Y-m-d',strtotime($this->today.' -1 day')).' 23:55:01', 2);
        $this->addTrip(107, 'NEXT-DAY-ROLLOVER', 1, $this->today.' 23:55:01');
        session()->set(['isLoggedIn'=>true, 'role'=>'staff', 'id'=>123, 'full_name'=>'Test Dispatcher']);
    }

    protected function tearDown(): void
    {
        service('renderer')->resetData();
        (new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances'))->setValue(null, $this->connections);
        $this->departureDb->close();
        parent::tearDown();
    }

    private function addTrip(int $id, string $plate, int $route, string $time, int $passengers=1): void
    {
        $this->departureDb->table('queue')->insert(['id'=>$id, 'vehicle_id'=>1, 'route_id'=>$route,
            'status'=>'departed', 'departure_time'=>$time, 'plate_number'=>$plate,
            'driver_name'=>'Snapshot Driver', 'operator_name'=>'Snapshot Operator', 'current_passengers'=>$passengers]);
    }

    private function page(string $method, array $query=[])
    {
        service('renderer')->resetData();
        \Config\Services::resetSingle('pager');
        $this->request->setGlobal('get',$query);
        return $this->withUri('http://localhost/staff/departures'.($method === 'report' ? '/print' : '').'?'.http_build_query($query))
            ->controller(Departures::class)->execute($method)->response();
    }

    public function testTodayPagePaginatesAllAssignedDeparturesAndShowsFullDayTotals(): void
    {
        $response = $this->page('index');
        $this->assertSame(200,$response->getStatusCode());
        $this->assertStringContainsString('no-store',$response->getHeaderLine('Cache-Control'));
        $html = $response->getBody();
        $this->assertStringContainsString('Generate Report',$html);
        $this->assertStringContainsString('37</div>',$html);
        $this->assertStringContainsString('357</div>',$html);
        $this->assertStringContainsString('ASSIGNED-SIBLING',$html);
        $second = $this->page('index',['page'=>'2'])->getBody();
        $this->assertStringContainsString('TODAY-01',$second);
        $this->assertStringContainsString('MIDNIGHT-ROLLOVER',$second);
        $this->assertStringNotContainsString('UNASSIGNED-ROUTE',$html.$second);
        $this->assertStringNotContainsString('YESTERDAY-TRIP',$html.$second);
        $this->assertStringNotContainsString('name="from_date"',$html);
    }

    public function testReportIncludesMoreThanThirtyTripsButCannotWidenTheDateOrRouteScope(): void
    {
        $response = $this->page('report',['from_date'=>'2000-01-01','to_date'=>'2099-12-31','route_id'=>'3','user_id'=>'999']);
        $this->assertSame(200,$response->getStatusCode());
        $this->assertStringContainsString('no-store',$response->getHeaderLine('Cache-Control'));
        $html = str_replace('\\/', '/', $response->getBody());
        foreach (['TODAY-01','TODAY-35','ASSIGNED-SIBLING','MIDNIGHT-ROLLOVER','37 Departures','Today only','/staff/departures'] as $text) $this->assertStringContainsString($text,$html);
        foreach (['YESTERDAY-TRIP','TOMORROW-TRIP','UNASSIGNED-ROUTE','UNASSIGNED-TERMINAL','WAITING-TRIP','NEXT-DAY-ROLLOVER','/admin/history'] as $text) $this->assertStringNotContainsString($text,$html);
        $this->assertStringContainsString(history_departure_time($this->today.' 00:00:00','M d, Y'),$html);
    }

    public function testSearchAndReportFiltersPreserveSnapshotDetailsAndAssignmentRestrictions(): void
    {
        $html = $this->page('report',['q'=>'TODAY-01','destination'=>'ORMOC','vehicle_type'=>'van'])->getBody();
        $this->assertStringContainsString('1 Departures',$html);
        $this->assertStringContainsString('TODAY-01',$html);
        $this->assertStringContainsString('Snapshot Driver',$html);
        $this->assertStringNotContainsString('TODAY-02',$html);
        $empty = $this->page('report',['destination'=>'TACLOBAN'])->getBody();
        $this->assertStringContainsString('0 Departures',$empty);
        $this->assertStringNotContainsString('UNASSIGNED-ROUTE',$empty);
    }

    public function testDispatcherWithoutAssignedRoutesCannotReadOrReportAnyTrips(): void
    {
        $this->departureDb->table('user_routes')->emptyTable();
        foreach (['index','report'] as $method) {
            $html = $this->page($method)->getBody();
            foreach (['TODAY-01','ASSIGNED-SIBLING','UNASSIGNED-ROUTE'] as $text) $this->assertStringNotContainsString($text,$html);
        }
    }
}
