<?php

namespace Tests\Unit;

use App\Controllers\Admin\Settings;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use Config\Database;

class OperationsQueueResetHarness extends Settings
{
    protected function logActivity(string $action, string $details): void {}
    protected function broadcastUpdate(string $type, array $data = []): void {}
}

final class OperationsQueueResetTest extends CIUnitTestCase
{
    use ControllerTestTrait;
    private $operationsDb;
    private array $connections;

    protected function setUp(): void
    {
        parent::setUp();
        $this->operationsDb = Database::connect(['DBDriver'=>'SQLite3','database'=>':memory:','DBPrefix'=>'','DBDebug'=>true], false);
        $property = new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances');
        $this->connections = $property->getValue();
        $property->setValue(null, array_replace($this->connections, ['default'=>$this->operationsDb,'tests'=>$this->operationsDb]));
        $this->operationsDb->query('CREATE TABLE system_settings (id INTEGER PRIMARY KEY AUTOINCREMENT, setting_key TEXT UNIQUE, setting_value TEXT, created_at TEXT, updated_at TEXT)');
        $this->operationsDb->query('CREATE TABLE queue (id INTEGER PRIMARY KEY, status TEXT, arrival_time TEXT, departure_time TEXT)');
        $this->operationsDb->table('queue')->insert(['id'=>1,'status'=>'waiting','arrival_time'=>date('Y-m-d H:i:s')]);
        get_all_system_settings(true);
        session()->set(['isLoggedIn'=>true,'id'=>1,'role'=>'super_admin']);
        $this->request->setMethod('POST')->setHeader('X-Requested-With', 'XMLHttpRequest');
    }

    protected function tearDown(): void
    {
        (new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances'))->setValue(null, $this->connections);
        get_all_system_settings(true);
        $this->operationsDb->close();
        parent::tearDown();
    }

    private function save(array $changes = [])
    {
        $this->withUri('http://localhost/admin/settings/update-operations');
        $this->request->setMethod('POST')->setHeader('X-Requested-With', 'XMLHttpRequest');
        $this->request->setGlobal('post', array_replace([
            'vehicle_cooldown_minutes'=>'30','vehicle_cooldown_unit'=>'minutes',
            'log_retention_days'=>'60','departure_retention_days'=>'60',
            'queue_reset_configured'=>'1','queue_reset_enabled'=>'1','queue_reset_time'=>'18:30',
        ], $changes));
        return $this->controller(OperationsQueueResetHarness::class)->execute('updateOperations');
    }

    public function testSuperadminCanSaveTheTimeWithoutImmediatelyClearingVehicles(): void
    {
        $result = $this->save();
        $this->assertSame(200, $result->response()->getStatusCode());
        $settings = array_column($this->operationsDb->table('system_settings')->get()->getResultArray(), 'setting_value', 'setting_key');
        $this->assertSame('18:30', $settings['queue_reset_time']);
        $this->assertSame('1', $settings['queue_reset_enabled']);
        $this->assertGreaterThan(0, (int) $settings['queue_reset_effective_from']);
        $this->assertSame('waiting', $this->operationsDb->table('queue')->get()->getRowArray()['status']);
    }

    public function testInvalidTimesAndRegularAdminsCannotChangeTheSchedule(): void
    {
        $this->assertSame(400, $this->save(['queue_reset_time'=>'25:99'])->response()->getStatusCode());
        $this->assertSame(0, $this->operationsDb->table('system_settings')->countAllResults());
        session()->set('role', 'admin');
        $this->response = service('response', null, false);
        $this->assertSame(403, $this->save()->response()->getStatusCode());
        $this->assertSame(0, $this->operationsDb->table('system_settings')->countAllResults());
    }
}
