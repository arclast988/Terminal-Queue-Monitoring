<?php

namespace Tests\Unit;

use App\Controllers\Admin\DepartureRules;
use App\Models\DepartureRuleModel;
use App\Models\RouteModel;
use App\Models\TerminalModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use Config\Database;

class ScopedDepartureRulesHarness extends DepartureRules
{
    public static BaseConnection $testDb;
    public static array $assignedIds = [1, 2];
    public static array $recalculations = [];
    public static array $broadcasts = [];

    public function __construct()
    {
        $this->ruleModel = new DepartureRuleModel(self::$testDb);
        $this->routeModel = new RouteModel(self::$testDb);
        $this->terminalModel = new TerminalModel(self::$testDb);
    }

    protected function assignedRouteIds(): ?array
    {
        return session()->get('role') === 'staff' ? self::$assignedIds : null;
    }

    protected function recalculateRuleScope(int $terminalId, ?int $routeId): void
    {
        self::$recalculations[] = [$terminalId, $routeId];
    }

    protected function logActivity(string $action, string $details): void
    {
    }

    protected function broadcastUpdate(string $type, array $data = []): void
    {
        self::$broadcasts[] = [$type, $data];
    }
}

final class DepartureRuleAccessTest extends CIUnitTestCase
{
    use ControllerTestTrait;
    private array $previousConnections;

    protected function setUp(): void
    {
        parent::setUp();
        ScopedDepartureRulesHarness::$testDb = Database::connect([
            'DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true,
        ], false);
        // Keep validation and model queries in this isolated database, then restore
        // the shared connections so later tests keep their configured database.
        $connections = new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances');
        $this->previousConnections = $connections->getValue();
        $connections->setValue(null, array_replace($this->previousConnections, [
            'default' => ScopedDepartureRulesHarness::$testDb, 'tests' => ScopedDepartureRulesHarness::$testDb,
        ]));
        ScopedDepartureRulesHarness::$assignedIds = [1, 2];
        ScopedDepartureRulesHarness::$recalculations = [];
        ScopedDepartureRulesHarness::$broadcasts = [];
        ScopedDepartureRulesHarness::$testDb->query('CREATE TABLE terminals (id INTEGER PRIMARY KEY, name TEXT)');
        ScopedDepartureRulesHarness::$testDb->table('terminals')->insertBatch([
            ['id' => 1, 'name' => 'VILLABA'], ['id' => 2, 'name' => 'OTHER TERMINAL'],
        ]);
        ScopedDepartureRulesHarness::$testDb->query('CREATE TABLE routes (id INTEGER PRIMARY KEY, terminal_id INTEGER, destination TEXT, status TEXT)');
        ScopedDepartureRulesHarness::$testDb->table('routes')->insertBatch([
            ['id' => 1, 'terminal_id' => 1, 'destination' => 'ORMOC', 'status' => 'active'],
            ['id' => 2, 'terminal_id' => 1, 'destination' => 'ORMOC', 'status' => 'active'],
            ['id' => 3, 'terminal_id' => 1, 'destination' => 'PALOMPON', 'status' => 'active'],
        ]);
        ScopedDepartureRulesHarness::$testDb->query('CREATE TABLE departure_rules (
            id INTEGER PRIMARY KEY AUTOINCREMENT, terminal_id INTEGER, route_id INTEGER,
            time_from TEXT DEFAULT "05:00:00", time_to TEXT DEFAULT "17:00:00", wait_minutes INTEGER DEFAULT 20,
            label TEXT DEFAULT "All Day", day_of_week INTEGER, round_number INTEGER DEFAULT 2,
            created_at TEXT, updated_at TEXT
        )');
        ScopedDepartureRulesHarness::$testDb->table('departure_rules')->insertBatch([
            ['id' => 1, 'terminal_id' => 1, 'route_id' => 1],
            ['id' => 2, 'terminal_id' => 1, 'route_id' => 2],
            ['id' => 3, 'terminal_id' => 1, 'route_id' => 3],
            ['id' => 4, 'terminal_id' => 1, 'route_id' => null],
            ['id' => 5, 'terminal_id' => 2, 'route_id' => null],
        ]);
        session()->set(['isLoggedIn' => true, 'id' => 123, 'role' => 'staff']);
    }

    protected function tearDown(): void
    {
        (new \ReflectionProperty(\CodeIgniter\Database\Config::class, 'instances'))->setValue(null, $this->previousConnections);
        ScopedDepartureRulesHarness::$testDb->close();
        parent::tearDown();
    }

    public function testDispatcherMayManageAssignedDestinationsAndTheirVehicleTypes(): void
    {
        $controller = new ScopedDepartureRulesHarness();
        $method = new \ReflectionMethod(DepartureRules::class, 'canManageRule');
        $this->assertTrue($method->invoke($controller, ['route_id' => 1]));
        $this->assertTrue($method->invoke($controller, ['route_id' => 2]));
        $this->assertFalse($method->invoke($controller, ['route_id' => 3]));
        $this->assertTrue($method->invoke($controller, ['terminal_id' => 1, 'route_id' => null]));
        $this->assertFalse($method->invoke($controller, ['terminal_id' => 2, 'route_id' => null]));
        ScopedDepartureRulesHarness::$assignedIds = [];
        $this->assertFalse($method->invoke($controller, ['terminal_id' => 1, 'route_id' => null]));
        foreach (['admin', 'superadmin'] as $role) {
            session()->set('role', $role);
            $this->assertTrue($method->invoke($controller, ['terminal_id' => 2, 'route_id' => null]));
        }
    }

    public function testEditingAnUnassignedRuleReturns403(): void
    {
        $result = $this->withUri('http://localhost/staff/departure-rules/edit/3')
            ->controller(ScopedDepartureRulesHarness::class)->execute('edit', 3);
        $this->assertSame(403, $result->response()->getStatusCode());
    }

    public function testUpdatingAnUnassignedRuleReturns403BeforeAcceptingNewValues(): void
    {
        $result = $this->withUri('http://localhost/staff/departure-rules/update/3')
            ->controller(ScopedDepartureRulesHarness::class)->execute('update', 3);
        $this->assertSame(403, $result->response()->getStatusCode());
        $this->assertSame(3, (int) ScopedDepartureRulesHarness::$testDb->table('departure_rules')->where('id', 3)->get()->getRowArray()['route_id']);
    }

    public function testDispatcherCanDeleteTheirTerminalDefaultAndRefreshTheTerminalQueue(): void
    {
        $result = $this->withUri('http://localhost/staff/departure-rules/delete/4')
            ->controller(ScopedDepartureRulesHarness::class)->execute('delete', 4);
        $this->assertSame(302, $result->response()->getStatusCode());
        $this->assertSame(0, ScopedDepartureRulesHarness::$testDb->table('departure_rules')->where('id', 4)->countAllResults());
        $this->assertSame([[1, null]], ScopedDepartureRulesHarness::$recalculations);
        $this->assertSame([['queue_update', ['action' => 'recalculate']]], ScopedDepartureRulesHarness::$broadcasts);
    }

    public function testOtherTerminalDefaultsCannotBeEditedUpdatedOrDeleted(): void
    {
        foreach (['edit', 'update', 'delete'] as $action) {
            $this->response = service('response', null, false);
            $result = $this->withUri('http://localhost/staff/departure-rules/' . $action . '/5')
                ->controller(ScopedDepartureRulesHarness::class)->execute($action, 5);
            $this->assertSame(403, $result->response()->getStatusCode());
        }
        $this->assertSame(1, ScopedDepartureRulesHarness::$testDb->table('departure_rules')->where('id', 5)->countAllResults());
        $this->assertSame([], ScopedDepartureRulesHarness::$recalculations);
    }

    public function testDispatcherCanCreateATerminalDefaultWithDayAndRound(): void
    {
        $this->postRule(['day_of_week' => '1', 'round_number' => '3']);
        $result = $this->withUri('http://localhost/staff/departure-rules/store')
            ->controller(ScopedDepartureRulesHarness::class)->execute('store');
        $this->assertSame(302, $result->response()->getStatusCode());
        $saved = ScopedDepartureRulesHarness::$testDb->table('departure_rules')->where('round_number', 3)->get()->getRowArray();
        $this->assertNotNull($saved);
        $this->assertNull($saved['route_id']);
        $this->assertSame(1, (int) $saved['terminal_id']);
        $this->assertSame(1, (int) $saved['day_of_week']);
        $this->assertSame(25, (int) $saved['wait_minutes']);
        $this->assertSame([[1, null]], ScopedDepartureRulesHarness::$recalculations);
    }

    public function testDispatcherCanUpdateTheirTerminalDefaultWithoutChoosingADestination(): void
    {
        $this->postRule();
        $result = $this->withUri('http://localhost/staff/departure-rules/update/4')
            ->controller(ScopedDepartureRulesHarness::class)->execute('update', 4);
        $this->assertSame(302, $result->response()->getStatusCode());
        $saved = ScopedDepartureRulesHarness::$testDb->table('departure_rules')->where('id', 4)->get()->getRowArray();
        $this->assertNull($saved['route_id']);
        $this->assertSame(25, (int) $saved['wait_minutes']);
        $this->assertSame([[1, null]], ScopedDepartureRulesHarness::$recalculations);
        $this->assertSame([['queue_update', ['action' => 'recalculate']]], ScopedDepartureRulesHarness::$broadcasts);
    }

    public function testDispatcherCannotCreateADefaultInAnotherTerminal(): void
    {
        $this->postRule(['terminal_id' => '2', 'round_number' => '3']);
        $result = $this->withUri('http://localhost/staff/departure-rules/store')
            ->controller(ScopedDepartureRulesHarness::class)->execute('store');
        $this->assertSame(403, $result->response()->getStatusCode());
        $this->assertSame(5, ScopedDepartureRulesHarness::$testDb->table('departure_rules')->countAllResults());
    }

    public function testDispatcherCannotMoveTheirDefaultToAnotherTerminal(): void
    {
        $this->postRule(['terminal_id' => '2']);
        $result = $this->withUri('http://localhost/staff/departure-rules/update/4')
            ->controller(ScopedDepartureRulesHarness::class)->execute('update', 4);
        $this->assertSame(403, $result->response()->getStatusCode());
        $saved = ScopedDepartureRulesHarness::$testDb->table('departure_rules')->where('id', 4)->get()->getRowArray();
        $this->assertSame(1, (int) $saved['terminal_id']);
        $this->assertSame(20, (int) $saved['wait_minutes']);
    }

    public function testDispatcherListShowsEditAndDeleteForTerminalDefaults(): void
    {
        $result = $this->withUri('http://localhost/staff/departure-rules')
            ->controller(ScopedDepartureRulesHarness::class)->execute('index');
        $html = $result->response()->getBody();
        $this->assertSame(200, $result->response()->getStatusCode());
        $this->assertStringContainsString('/staff/departure-rules/edit/4', $html);
        $this->assertStringContainsString('/staff/departure-rules/delete/4', $html);
        $this->assertStringNotContainsString('View Only', $html);
        $this->assertStringNotContainsString('/staff/departure-rules/edit/5', $html);
    }

    public function testDispatcherCreateAndEditFormsAllowBlankDestination(): void
    {
        foreach (['create', 'edit'] as $action) {
            $this->response = service('response', null, false);
            $result = $this->withUri('http://localhost/staff/departure-rules/' . $action . '/4')
                ->controller(ScopedDepartureRulesHarness::class)->execute($action, ...($action === 'edit' ? [4] : []));
            $html = $result->response()->getBody();
            $this->assertSame(200, $result->response()->getStatusCode());
            $this->assertMatchesRegularExpression('/<select[^>]*id="route_id"[^>]*>/', $html);
            $this->assertDoesNotMatchRegularExpression('/<select[^>]*id="route_id"[^>]*required/', $html);
            $this->assertStringContainsString('leave blank for a terminal-wide default', $html);
        }
    }

    private function postRule(array $overrides = []): void
    {
        $this->request->setMethod('POST');
        $this->request->setGlobal('post', array_replace([
            'terminal_id' => '1', 'route_id' => '', 'time_from' => '05:00', 'time_to' => '17:00',
            'wait_duration' => '00:25', 'day_of_week' => '', 'round_number' => '2', 'label' => 'Afternoon',
            'return_route' => 'default',
        ], $overrides));
    }

    public function testUpdatingAMissingRuleReturns404(): void
    {
        $result = $this->withUri('http://localhost/staff/departure-rules/update/99')
            ->controller(ScopedDepartureRulesHarness::class)->execute('update', 99);
        $this->assertSame(404, $result->response()->getStatusCode());
    }
}
