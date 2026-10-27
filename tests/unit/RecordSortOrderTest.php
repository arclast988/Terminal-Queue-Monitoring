<?php

namespace Tests\Unit;

use App\Controllers\Admin\History;
use App\Controllers\Admin\Logs;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use Config\Database;

final class RecordSortOrderTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    private BaseConnection $recordsDb;
    private array $connections;
    private string $today;

    protected function setUp(): void
    {
        parent::setUp();
        $this->today = date('Y-m-d');
        $this->recordsDb = Database::connect(['DBDriver'=>'SQLite3', 'database'=>':memory:', 'DBPrefix'=>'', 'DBDebug'=>true], false);
        $property = new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances');
        $this->connections = $property->getValue();
        $property->setValue(null, array_replace($this->connections, ['default'=>$this->recordsDb, 'tests'=>$this->recordsDb]));
        $db = $this->recordsDb;
        foreach ([
            'terminals (id INTEGER PRIMARY KEY, name TEXT)',
            'routes (id INTEGER PRIMARY KEY, terminal_id INTEGER, destination TEXT, vehicle_type TEXT)',
            'vehicles (id INTEGER PRIMARY KEY, plate_number TEXT, driver_name TEXT, operator_name TEXT, owner_name TEXT, type TEXT, photo TEXT)',
            'vehicle_types (id INTEGER PRIMARY KEY, slug TEXT, name TEXT, color TEXT, icon TEXT, photo TEXT, is_active INTEGER)',
            'queue (id INTEGER PRIMARY KEY, vehicle_id INTEGER, route_id INTEGER, status TEXT, departure_time TEXT, plate_number TEXT, driver_name TEXT, operator_name TEXT, current_passengers INTEGER)',
            'users (id INTEGER PRIMARY KEY, username TEXT, full_name TEXT, role TEXT, email TEXT)',
            'audit_logs (id INTEGER PRIMARY KEY, user_id INTEGER, action TEXT, details TEXT, timestamp TEXT)',
        ] as $schema) $db->query('CREATE TABLE '.$schema);
        $db->table('terminals')->insert(['id'=>1, 'name'=>'VILLABA']);
        $db->table('routes')->insert(['id'=>1, 'terminal_id'=>1, 'destination'=>'ORMOC', 'vehicle_type'=>'van']);
        $db->table('vehicles')->insert(['id'=>1, 'plate_number'=>'LIVE', 'driver_name'=>'Driver', 'operator_name'=>'Operator', 'type'=>'van']);
        $db->table('users')->insert(['id'=>1, 'username'=>'sort-user', 'full_name'=>'Sort User', 'role'=>'admin', 'email'=>'sort@example.com']);
        // IDs deliberately disagree with time; IDs 2 and 3 have identical times.
        foreach ([1=>'11:00:00', 2=>'10:00:00', 3=>'10:00:00'] as $id=>$time) {
            $db->table('queue')->insert(['id'=>$id, 'vehicle_id'=>1, 'route_id'=>1, 'status'=>'departed',
                'departure_time'=>$this->today.' '.$time, 'plate_number'=>'SORT-RECORD-'.$id,
                'driver_name'=>'Driver', 'operator_name'=>'Operator', 'current_passengers'=>[1=>0, 2=>2, 3=>12][$id]]);
            $db->table('audit_logs')->insert(['id'=>$id, 'user_id'=>1, 'action'=>'TEST_RECORD',
                'details'=>'SORT-RECORD-'.$id, 'timestamp'=>$this->today.' '.$time]);
        }
        session()->set(['isLoggedIn'=>true, 'id'=>1, 'role'=>'admin', 'full_name'=>'Sort User']);
    }

    protected function tearDown(): void
    {
        service('renderer')->resetData();
        (new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances'))->setValue(null, $this->connections);
        $this->recordsDb->close();
        parent::tearDown();
    }

    private function page(string $controller, string $method, array $query): string
    {
        service('renderer')->resetData();
        \Config\Services::resetSingle('pager');
        $this->response = service('response', null, false);
        $path = $controller === History::class ? 'history' : 'logs';
        $this->withUri('http://localhost/admin/'.$path.($method === 'print' ? '/print' : '').'?'.http_build_query($query));
        $this->request->setGlobal('get', $query);
        $response = $this->controller($controller)->execute($method)->response();
        $this->assertSame(200, $response->getStatusCode());
        return $response->getBody();
    }

    private function rowMarkers(string $html): array
    {
        $previous = libxml_use_internal_errors(true);
        try {
            $document = new \DOMDocument();
            $document->loadHTML($html);
            $markers = [];
            foreach ((new \DOMXPath($document))->query('//tbody/tr') as $row) {
                if (preg_match('/SORT-RECORD-\d+/', $row->textContent, $match)) $markers[] = $match[0];
            }
            return $markers;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    public function testListsAndPrintoutsUseTheSameTimeOrderAndStableTies(): void
    {
        foreach ([History::class, Logs::class] as $controller) {
            foreach (['asc'=>[2,3,1], 'desc'=>[1,3,2]] as $direction=>$ids) {
                $query = ['sort_order'=>$direction, 'from_date'=>$this->today, 'to_date'=>$this->today, 'q'=>'SORT-RECORD'];
                if ($controller === History::class) $query += ['destination'=>'ORMOC', 'vehicle_type'=>'van'];
                else $query['user_id'] = '1';
                $expected = array_map(static fn($id) => 'SORT-RECORD-'.$id, $ids);
                foreach (['index', 'print'] as $method) {
                    $html = $this->page($controller, $method, $query);
                    $this->assertSame($expected, $this->rowMarkers($html), "$controller $method $direction");
                    $this->assertStringContainsString($direction === 'asc' ? 'Oldest first (Ascending)' : 'Newest first (Descending)', $html);
                }
            }
        }
    }

    public function testInvalidOrMissingDirectionsFallBackToNewestFirstAndFiltersStillApply(): void
    {
        foreach ([History::class, Logs::class] as $controller) {
            foreach (['index', 'print'] as $method) {
                foreach ([null, 'asc; DROP TABLE queue', ['asc']] as $direction) {
                    $query = $direction === null ? [] : ['sort_order'=>$direction];
                    $this->assertSame(['SORT-RECORD-1','SORT-RECORD-3','SORT-RECORD-2'], $this->rowMarkers($this->page($controller, $method, $query)));
                }
                $this->assertSame(['SORT-RECORD-2'], $this->rowMarkers($this->page($controller, $method, ['q'=>'SORT-RECORD-2', 'sort_order'=>'asc'])));
            }
        }
    }

    public function testPaginationRetainsTheOrderAndReturnsTheNextRecords(): void
    {
        for ($id=4; $id<=31; $id++) {
            $this->recordsDb->table('audit_logs')->insert(['id'=>$id, 'user_id'=>1, 'action'=>'TEST_RECORD',
                'details'=>'SORT-RECORD-'.$id, 'timestamp'=>$this->today.' 12:00:00']);
        }
        $html = $this->page(Logs::class, 'index', ['sort_order'=>'asc', 'page'=>'2']);
        $this->assertSame(array_map(static fn($id) => 'SORT-RECORD-'.$id, range(26,31)), $this->rowMarkers($html));
        $this->assertStringContainsString('sort_order=asc', $html);
    }
}
