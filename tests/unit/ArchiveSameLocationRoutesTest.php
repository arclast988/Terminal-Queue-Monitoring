<?php

namespace Tests\Unit;

use App\Database\Migrations\ArchiveSameLocationRoutes;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

final class ArchiveSameLocationRoutesTest extends CIUnitTestCase
{
    private BaseConnection $archiveDb;

    protected function setUp(): void
    {
        parent::setUp();
        require_once APPPATH . 'Database/Migrations/2026-11-19-000000_ArchiveSameLocationRoutes.php';
        $this->archiveDb = Database::connect(['DBDriver'=>'SQLite3', 'database'=>':memory:', 'DBPrefix'=>'', 'DBDebug'=>true, 'foreignKeys'=>true], false);
        $db = $this->archiveDb;
        $db->query('CREATE TABLE terminals (id INTEGER PRIMARY KEY, name TEXT)');
        $db->query('CREATE TABLE routes (id INTEGER PRIMARY KEY, terminal_id INTEGER REFERENCES terminals(id), destination TEXT, vehicle_type TEXT, status TEXT)');
        $db->query('CREATE TABLE vehicles (id INTEGER PRIMARY KEY, default_route_id INTEGER REFERENCES routes(id), plate_number TEXT, type TEXT, status TEXT, dispatch_order INTEGER, dispatch_rotation INTEGER, dispatch_rotation_date TEXT)');
        $db->query('CREATE TABLE queue (id INTEGER PRIMARY KEY, route_id INTEGER REFERENCES routes(id), vehicle_id INTEGER REFERENCES vehicles(id), status TEXT, position INTEGER, boarding_start TEXT, estimated_departure TEXT, departure_time TEXT, current_passengers INTEGER)');
        $db->query('CREATE TABLE fares (id INTEGER PRIMARY KEY, route_id INTEGER REFERENCES routes(id), amount REAL)');
        $db->query('CREATE TABLE user_routes (id INTEGER PRIMARY KEY, route_id INTEGER REFERENCES routes(id), user_id INTEGER)');
        $db->query('CREATE TABLE audit_logs (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, action TEXT, details TEXT, timestamp TEXT)');
        $db->table('terminals')->insertBatch([['id'=>1,'name'=>'ORMOC'], ['id'=>2,'name'=>' Ormoc  City ']]);
        $db->table('routes')->insertBatch([
            ['id'=>1,'terminal_id'=>1,'destination'=>' ormoc ','vehicle_type'=>'minibus','status'=>'active'],
            ['id'=>2,'terminal_id'=>1,'destination'=>'ISABEL','vehicle_type'=>'van','status'=>'active'],
            ['id'=>3,'terminal_id'=>2,'destination'=>"ormoc\u{00A0}city",'vehicle_type'=>'van','status'=>'active'],
            ['id'=>4,'terminal_id'=>1,'destination'=>'ORMOC','vehicle_type'=>'van','status'=>'archived'],
        ]);
        foreach ([1=>1, 2=>2, 3=>3, 4=>4] as $vehicleId=>$routeId) {
            $db->table('vehicles')->insert(['id'=>$vehicleId,'default_route_id'=>$routeId,'plate_number'=>'ABC-'.$vehicleId,'type'=>'van','status'=>'active','dispatch_order'=>1,'dispatch_rotation'=>1,'dispatch_rotation_date'=>'2026-11-19']);
        }
        foreach ([[1,1,1,'waiting'], [2,1,1,'boarding'], [3,1,1,'departed'], [4,2,2,'waiting'], [5,3,3,'boarding'], [6,4,4,'waiting']] as [$id,$routeId,$vehicleId,$status]) {
            $db->table('queue')->insert(['id'=>$id,'route_id'=>$routeId,'vehicle_id'=>$vehicleId,'status'=>$status,'position'=>1,'boarding_start'=>'2026-11-19 09:00:00','estimated_departure'=>'2026-11-19 09:30:00','departure_time'=>$status==='departed'?'2026-11-19 09:35:00':null,'current_passengers'=>9]);
        }
        foreach ([1,2,3,4] as $routeId) {
            $db->table('fares')->insert(['id'=>$routeId,'route_id'=>$routeId,'amount'=>100+$routeId]);
            $db->table('user_routes')->insert(['id'=>$routeId,'route_id'=>$routeId,'user_id'=>123]);
        }
    }

    protected function tearDown(): void
    {
        $this->archiveDb->close();
        parent::tearDown();
    }

    private function migration(): ArchiveSameLocationRoutes
    {
        return new ArchiveSameLocationRoutes(Database::forge($this->archiveDb));
    }

    private function snapshot(): array
    {
        $rows=[];
        foreach (['routes','vehicles','queue','fares','user_routes','audit_logs'] as $table) {
            $rows[$table]=$this->archiveDb->table($table)->orderBy('id')->get()->getResultArray();
        }
        return $rows;
    }

    public function testCleanupArchivesEveryInvalidVariantAndPreservesHistoryAndValidRoutes(): void
    {
        $before=$this->snapshot();
        $this->migration()->up();
        $after=$this->snapshot();
        $this->assertCount(4, $after['routes']);
        $this->assertSame(['archived','active','archived','archived'], array_column($after['routes'],'status'));
        $this->assertSame($before['routes'][1], $after['routes'][1]);
        $this->assertSame($before['vehicles'][1], $after['vehicles'][1]);
        $this->assertSame($before['queue'][2], $after['queue'][2]);
        $this->assertSame($before['queue'][3], $after['queue'][3]);
        $this->assertSame($before['fares'], $after['fares']);
        $this->assertSame($before['user_routes'], $after['user_routes']);
        foreach ([0,2,3] as $index) {
            $this->assertNull($after['vehicles'][$index]['default_route_id']);
            $this->assertSame(0, (int)$after['vehicles'][$index]['dispatch_order']);
            $this->assertSame(0, (int)$after['vehicles'][$index]['dispatch_rotation']);
            $this->assertNull($after['vehicles'][$index]['dispatch_rotation_date']);
        }
        foreach ([0,1,4,5] as $index) {
            $this->assertSame('canceled',$after['queue'][$index]['status']);
            $this->assertSame(0,(int)$after['queue'][$index]['position']);
            $this->assertNull($after['queue'][$index]['boarding_start']);
            $this->assertNull($after['queue'][$index]['estimated_departure']);
        }
        $this->assertCount(1,$after['audit_logs']);
        $this->assertStringContainsString('route IDs: 1, 3, 4',$after['audit_logs'][0]['details']);
        $this->assertStringContainsString('canceled active trip IDs: 1, 2, 5, 6',$after['audit_logs'][0]['details']);
        $this->migration()->up();
        $this->assertSame($after,$this->snapshot(),'Repeating the migration must not add logs or change repaired data.');
        $this->migration()->down();
        $this->assertSame($after,$this->snapshot(),'Rollback must not restore impossible routes or canceled trips.');
    }

    public function testFailureRollsBackAllChanges(): void
    {
        $this->archiveDb->query("CREATE TRIGGER reject_cleanup_log BEFORE INSERT ON audit_logs BEGIN SELECT RAISE(ABORT, 'simulated audit failure'); END");
        $before=$this->snapshot();
        try {
            $this->migration()->up();
            $this->fail('Expected migration failure.');
        } catch (\Throwable $error) {
            $this->assertStringContainsString('Could not archive invalid same-location routes.',$error->getMessage());
        }
        $this->assertSame($before,$this->snapshot(),'An incomplete cleanup must leave every original row intact.');
    }

    public function testLegacySchemaWithoutDispatchTimingOrAuditColumnsStillArchivesSafely(): void
    {
        $db=Database::connect(['DBDriver'=>'SQLite3','database'=>':memory:','DBPrefix'=>'','DBDebug'=>true],false);
        $db->query('CREATE TABLE terminals (id INTEGER PRIMARY KEY, name TEXT)');
        $db->query('CREATE TABLE routes (id INTEGER PRIMARY KEY, terminal_id INTEGER, destination TEXT, status TEXT)');
        $db->query('CREATE TABLE vehicles (id INTEGER PRIMARY KEY, default_route_id INTEGER)');
        $db->query('CREATE TABLE queue (id INTEGER PRIMARY KEY, route_id INTEGER, status TEXT, position INTEGER)');
        $db->table('terminals')->insert(['id'=>1,'name'=>'ORMOC']);
        $db->table('routes')->insert(['id'=>1,'terminal_id'=>1,'destination'=>'ORMOC','status'=>'active']);
        $db->table('vehicles')->insert(['id'=>1,'default_route_id'=>1]);
        $db->table('queue')->insert(['id'=>1,'route_id'=>1,'status'=>'waiting','position'=>1]);
        $migration=new ArchiveSameLocationRoutes(Database::forge($db));
        $migration->up();
        $this->assertSame('archived',$db->table('routes')->get()->getRowArray()['status']);
        $this->assertNull($db->table('vehicles')->get()->getRowArray()['default_route_id']);
        $this->assertSame('canceled',$db->table('queue')->get()->getRowArray()['status']);
        $migration->up();
        $this->assertSame(1,$db->table('queue')->countAllResults());
        $db->close();
    }
}
