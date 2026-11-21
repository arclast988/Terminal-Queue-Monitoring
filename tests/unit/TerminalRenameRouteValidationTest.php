<?php

namespace Tests\Unit;

use App\Controllers\Admin\Terminals;
use App\Models\TerminalModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use Config\Database;

class TerminalRenameRouteHarness extends Terminals
{
    public static $db;
    public static int $logged = 0;
    public static int $broadcast = 0;
    public function __construct() { $this->terminalModel = new TerminalModel(self::$db); }
    protected function logActivity(string $action, string $details): void { self::$logged++; }
    protected function broadcastUpdate(string $type, array $data = []): void { self::$broadcast++; }
}

final class TerminalRenameRouteValidationTest extends CIUnitTestCase
{
    use ControllerTestTrait;
    private array $connections;

    protected function setUp(): void
    {
        parent::setUp();
        TerminalRenameRouteHarness::$db = Database::connect(['DBDriver'=>'SQLite3','database'=>':memory:','DBPrefix'=>'','DBDebug'=>true], false);
        $property = new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances');
        $this->connections = $property->getValue();
        $property->setValue(null, array_replace($this->connections, ['default'=>TerminalRenameRouteHarness::$db,'tests'=>TerminalRenameRouteHarness::$db]));
        $db = TerminalRenameRouteHarness::$db;
        $db->query('CREATE TABLE terminals (id INTEGER PRIMARY KEY, name TEXT, location TEXT, capacity INTEGER, created_at TEXT)');
        $db->query('CREATE TABLE routes (id INTEGER PRIMARY KEY, terminal_id INTEGER, destination TEXT, status TEXT, created_at TEXT)');
        $db->table('terminals')->insert(['id'=>1,'name'=>'VILLABA','location'=>'Leyte','capacity'=>20]);
        $db->table('routes')->insertBatch([
            ['id'=>1,'terminal_id'=>1,'destination'=>'ORMOC','status'=>'active'],
            ['id'=>2,'terminal_id'=>1,'destination'=>'Tacloban City','status'=>'active'],
            ['id'=>3,'terminal_id'=>1,'destination'=>'ISABEL','status'=>'archived'],
            ['id'=>4,'terminal_id'=>2,'destination'=>'PALOMPON','status'=>'active'],
        ]);
        TerminalRenameRouteHarness::$logged = TerminalRenameRouteHarness::$broadcast = 0;
    }

    protected function tearDown(): void
    {
        session()->remove(['errors','success','_ci_old_input']);
        (new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances'))->setValue(null, $this->connections);
        TerminalRenameRouteHarness::$db->close();
        parent::tearDown();
    }

    private function rename(string $name)
    {
        session()->remove(['errors','success','_ci_old_input']);
        service('validation')->reset();
        $post = ['name'=>$name,'location'=>'Leyte','capacity'=>'25'];
        $this->withUri('http://localhost/admin/terminals/update/1');
        $this->request->setMethod('POST')->setGlobal('post', $post)->setGlobal('request', $post);
        $this->request->setHeader('Referer', 'http://localhost/admin/terminals/edit/1');
        return $this->controller(TerminalRenameRouteHarness::class)->execute('update', 1)->response();
    }

    public function testTerminalCannotBeRenamedToAnActiveDestinationAndKeepsEveryRecordUnchanged(): void
    {
        $db = TerminalRenameRouteHarness::$db;
        $terminal = $db->table('terminals')->get()->getResultArray();
        $routes = $db->table('routes')->get()->getResultArray();
        foreach (['ORMOC','  oRmOc  '," tacloban\u{00A0}  CITY "] as $name) {
            $response = $this->rename($name);
            $this->assertSame(302, $response->getStatusCode());
            $this->assertStringContainsString('active route destinations', session()->getFlashdata('errors')['name']);
            $this->assertSame($name, session()->get('_ci_old_input')['post']['name']);
            $this->assertSame($terminal, $db->table('terminals')->get()->getResultArray());
            $this->assertSame($routes, $db->table('routes')->get()->getResultArray());
        }
        $this->assertSame(0, TerminalRenameRouteHarness::$logged);
        $this->assertSame(0, TerminalRenameRouteHarness::$broadcast);
    }

    public function testArchivedDestinationDoesNotPreventATerminalRename(): void
    {
        $this->assertSame(302, $this->rename('ISABEL')->getStatusCode());
        $this->assertNull(session()->getFlashdata('errors'));
        $this->assertSame('ISABEL', TerminalRenameRouteHarness::$db->table('terminals')->where('id',1)->get()->getRowArray()['name']);
        $this->assertSame(1, TerminalRenameRouteHarness::$logged);
        $this->assertSame(1, TerminalRenameRouteHarness::$broadcast);
    }

    public function testDestinationsBelongingToAnotherTerminalDoNotPreventARename(): void
    {
        $this->assertSame(302, $this->rename('PALOMPON')->getStatusCode());
        $this->assertNull(session()->getFlashdata('errors'));
        $this->assertSame('PALOMPON', TerminalRenameRouteHarness::$db->table('terminals')->where('id',1)->get()->getRowArray()['name']);
    }
}
