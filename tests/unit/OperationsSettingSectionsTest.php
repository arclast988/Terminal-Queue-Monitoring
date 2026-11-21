<?php

namespace Tests\Unit;

use App\Controllers\Admin\Settings;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use Config\Database;

class OperationsSettingSectionsHarness extends Settings
{
    public static array $events = [];
    public static array $activity = [];
    protected function logActivity(string $action, string $details): void { self::$activity[] = [$action, $details]; }
    protected function broadcastUpdate(string $type, array $data = []): void { self::$events[] = [$type, $data]; }
}

final class OperationsSettingSectionsTest extends CIUnitTestCase
{
    use ControllerTestTrait;
    private $operationsDb;
    private array $connections;
    private array $saved = [
        'vehicle_cooldown_minutes' => '30', 'log_retention_days' => '60', 'departure_retention_days' => '60',
        'queue_reset_enabled' => '1', 'queue_reset_time' => '18:30', 'queue_reset_effective_from' => '100',
        'app_name' => 'Saved terminal',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->operationsDb = Database::connect(['DBDriver'=>'SQLite3', 'database'=>':memory:', 'DBPrefix'=>'', 'DBDebug'=>true], false);
        $property = new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances');
        $this->connections = $property->getValue();
        $property->setValue(null, array_replace($this->connections, ['default'=>$this->operationsDb, 'tests'=>$this->operationsDb]));
        $this->operationsDb->query('CREATE TABLE system_settings (id INTEGER PRIMARY KEY AUTOINCREMENT, setting_key TEXT UNIQUE, setting_value TEXT, created_at TEXT, updated_at TEXT)');
        $this->operationsDb->query('CREATE TABLE audit_logs (id INTEGER PRIMARY KEY, user_id INTEGER, action TEXT, details TEXT, timestamp TEXT)');
        $this->operationsDb->query('CREATE TABLE queue (id INTEGER PRIMARY KEY, status TEXT, arrival_time TEXT, departure_time TEXT)');
        foreach ($this->saved as $key => $value) $this->operationsDb->table('system_settings')->insert(['setting_key'=>$key, 'setting_value'=>$value]);
        $old = date('Y-m-d H:i:s', strtotime('-20 days'));
        $this->operationsDb->table('audit_logs')->insert(['id'=>1, 'timestamp'=>$old]);
        $this->operationsDb->table('queue')->insertBatch([
            ['id'=>1, 'status'=>'waiting', 'arrival_time'=>date('Y-m-d H:i:s'), 'departure_time'=>null],
            ['id'=>2, 'status'=>'departed', 'arrival_time'=>$old, 'departure_time'=>$old],
        ]);
        get_all_system_settings(true);
        OperationsSettingSectionsHarness::$events = OperationsSettingSectionsHarness::$activity = [];
        session()->set(['isLoggedIn'=>true, 'id'=>1, 'role'=>'super_admin']);
    }

    protected function tearDown(): void
    {
        session()->remove('_ci_old_input');
        (new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances'))->setValue(null, $this->connections);
        get_all_system_settings(true);
        $this->operationsDb->close();
        parent::tearDown();
    }

    private function post(array $values, bool $ajax = true)
    {
        $this->response = service('response', null, false);
        $this->withUri('http://localhost/admin/settings/update-operations');
        $this->request->setMethod('POST')->setGlobal('post', $values);
        if ($ajax) $this->request->setHeader('X-Requested-With', 'XMLHttpRequest');
        else $this->request->removeHeader('X-Requested-With');
        return $this->controller(OperationsSettingSectionsHarness::class)->execute('updateOperations');
    }

    private function values(): array
    {
        return array_column($this->operationsDb->table('system_settings')->get()->getResultArray(), 'setting_value', 'setting_key');
    }

    public function testCooldownSaveIgnoresOtherInvalidDraftsAndDoesNotPurgeHistory(): void
    {
        $response = $this->post([
            'operations_section'=>'cooldown', 'vehicle_cooldown_minutes'=>'45',
            'log_retention_days'=>'invalid', 'departure_retention_days'=>'0', 'queue_reset_time'=>'25:99',
            'queue_reset_enabled'=>'0', 'queue_reset_configured'=>'1', 'app_name'=>'Unsaved identity',
        ])->response();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(array_replace($this->saved, ['vehicle_cooldown_minutes'=>'45']), $this->values());
        $this->assertSame(1, $this->operationsDb->table('audit_logs')->countAllResults());
        $this->assertSame(2, $this->operationsDb->table('queue')->countAllResults());
        $data = json_decode($response->getBody(), true);
        $this->assertSame(['vehicle_cooldown_minutes'=>45], $data['data']);
        $this->assertArrayHasKey('csrf_hash', $data);
        $this->assertSame(60, OperationsSettingSectionsHarness::$events[0][1]['log_retention_days']);
        $this->assertSame('18:30', OperationsSettingSectionsHarness::$events[0][1]['queue_reset_time']);
    }

    public function testDailySaveUpdatesTheToggleAndTimeTogetherWithoutClearingTheQueue(): void
    {
        $response = $this->post([
            'operations_section'=>'daily_clear', 'queue_reset_enabled'=>'0', 'queue_reset_time'=>'13:05',
            'vehicle_cooldown_minutes'=>'-99', 'log_retention_days'=>'bad',
        ])->response();
        $this->assertSame(200, $response->getStatusCode());
        $values = $this->values();
        $this->assertGreaterThan(100, (int) $values['queue_reset_effective_from']);
        $expected = array_replace($this->saved, ['queue_reset_enabled'=>'0', 'queue_reset_time'=>'13:05', 'queue_reset_effective_from'=>$values['queue_reset_effective_from']]);
        $this->assertSame($expected, $values);
        $this->assertSame(2, $this->operationsDb->table('queue')->countAllResults());
        $this->assertSame(1, $this->operationsDb->table('audit_logs')->countAllResults());
        $this->assertSame(['queue_reset_enabled'=>false, 'queue_reset_time'=>'13:05'], json_decode($response->getBody(), true)['data']);
    }

    public function testAuditRetentionOnlyPurgesAuditRecords(): void
    {
        $response = $this->post(['operations_section'=>'audit_retention', 'log_retention_days'=>'7', 'departure_retention_days'=>'1'])->response();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(array_replace($this->saved, ['log_retention_days'=>'7']), $this->values());
        $this->assertSame(0, $this->operationsDb->table('audit_logs')->countAllResults());
        $this->assertSame(2, $this->operationsDb->table('queue')->countAllResults());
    }

    public function testDepartureRetentionOnlyPurgesCompletedDepartureRecords(): void
    {
        $response = $this->post(['operations_section'=>'departure_retention', 'departure_retention_days'=>'7', 'log_retention_days'=>'1'])->response();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(array_replace($this->saved, ['departure_retention_days'=>'7']), $this->values());
        $this->assertSame(1, $this->operationsDb->table('audit_logs')->countAllResults());
        $this->assertSame([['id'=>1, 'status'=>'waiting']], $this->operationsDb->table('queue')->select('id, status')->get()->getResultArray());
    }

    public function testMissingInvalidAndUnknownSectionsCannotWriteAnySettings(): void
    {
        foreach ([
            ['operations_section'=>'cooldown'],
            ['operations_section'=>'cooldown', 'vehicle_cooldown_minutes'=>'bad'],
            ['operations_section'=>'cooldown', 'vehicle_cooldown_minutes'=>'10081'],
            ['operations_section'=>'audit_retention', 'log_retention_days'=>['7']],
            ['operations_section'=>'audit_retention', 'log_retention_days'=>'0'],
            ['operations_section'=>'departure_retention', 'departure_retention_days'=>'3651'],
            ['operations_section'=>'daily_clear', 'queue_reset_enabled'=>'0'],
            ['operations_section'=>'daily_clear', 'queue_reset_enabled'=>'1', 'queue_reset_time'=>'25:99'],
            ['operations_section'=>'daily_clear', 'queue_reset_enabled'=>'bad', 'queue_reset_time'=>'12:00'],
            ['operations_section'=>'identity', 'app_name'=>'Changed'],
            ['operations_section'=>['cooldown'], 'vehicle_cooldown_minutes'=>'45'],
        ] as $payload) {
            $response = $this->post($payload)->response();
            $this->assertSame(400, $response->getStatusCode(), json_encode($payload));
            $this->assertSame($this->saved, $this->values());
        }
        $this->assertSame([], OperationsSettingSectionsHarness::$events);
    }

    public function testUnchangedCardDoesNotWriteBroadcastOrResetTheSchedule(): void
    {
        $response = $this->post(['operations_section'=>'daily_clear', 'queue_reset_enabled'=>'1', 'queue_reset_time'=>'18:30'])->response();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue(json_decode($response->getBody(), true)['no_change']);
        $this->assertSame($this->saved, $this->values());
        $this->assertSame([], OperationsSettingSectionsHarness::$events);
        $this->assertSame([], OperationsSettingSectionsHarness::$activity);
    }

    public function testLegacySaveAllStillValidatesAndUpdatesEveryOperationalGroup(): void
    {
        $response = $this->post([
            'vehicle_cooldown_minutes'=>'1.5', 'vehicle_cooldown_unit'=>'hours',
            'log_retention_days'=>'90', 'departure_retention_days'=>'120',
            'queue_reset_configured'=>'1', 'queue_reset_enabled'=>'1', 'queue_reset_time'=>'18:30',
        ])->response();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(array_replace($this->saved, ['vehicle_cooldown_minutes'=>'90', 'log_retention_days'=>'90', 'departure_retention_days'=>'120']), $this->values());
        $this->assertCount(5, json_decode($response->getBody(), true)['data']);
    }

    public function testNativeCardSaveReturnsToOperationsAndRetainsOtherDraftValues(): void
    {
        $response = $this->post([
            'operations_section'=>'cooldown', 'vehicle_cooldown_minutes'=>'45', 'log_retention_days'=>'120',
        ], false)->response();
        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringEndsWith('/admin/settings#operations', $response->getHeaderLine('Location'));
        $this->assertSame(array_replace($this->saved, ['vehicle_cooldown_minutes'=>'45']), $this->values());
        $this->assertSame('120', session()->get('_ci_old_input')['post']['log_retention_days']);
    }

    public function testNativeValidationFailureReturnsToOperationsWithoutWriting(): void
    {
        $response = $this->post(['operations_section'=>'audit_retention', 'log_retention_days'=>'0'], false)->response();
        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringEndsWith('/admin/settings#operations', $response->getHeaderLine('Location'));
        $this->assertSame($this->saved, $this->values());
        $this->assertNotEmpty(session()->getFlashdata('error'));
    }

    public function testOtherRolesCannotSaveAnyOperationalCard(): void
    {
        foreach (['admin', 'staff'] as $role) {
            session()->set('role', $role);
            $response = $this->post(['operations_section'=>'cooldown', 'vehicle_cooldown_minutes'=>'45'])->response();
            $this->assertSame(403, $response->getStatusCode());
            $this->assertSame($this->saved, $this->values());
        }
    }
}
