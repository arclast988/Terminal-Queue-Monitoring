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
        service('validation')->reset();
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
            label TEXT DEFAULT "All Day", day_of_week INTEGER, days_of_week TEXT, round_number INTEGER DEFAULT 2,
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
        service('validation')->reset();
        $this->request->setMethod('POST');
        $this->request->setGlobal('post', array_replace([
            'terminal_id' => '1', 'route_id' => '', 'time_from' => '05:00', 'time_to' => '17:00',
            'wait_duration' => '00:25', 'day_of_week' => '', 'round_number' => '2', 'label' => 'Afternoon',
            'return_route' => 'default',
        ], $overrides));
    }

    public function testCheckedDaysAreStoredTogetherOnOneRule(): void
    {
        $this->postRule(['day_selection' => '1', 'days_of_week' => ['3', '1', '2', '2'], 'round_number' => '3']);
        $result = $this->withUri('http://localhost/staff/departure-rules/store')
            ->controller(ScopedDepartureRulesHarness::class)->execute('store');
        $this->assertSame(302, $result->response()->getStatusCode());
        $saved = ScopedDepartureRulesHarness::$testDb->table('departure_rules')->where('round_number', 3)->get()->getRowArray();
        $this->assertSame('1,2,3', $saved['days_of_week']);
        $this->assertNull($saved['day_of_week']);
        $this->assertSame(6, ScopedDepartureRulesHarness::$testDb->table('departure_rules')->countAllResults());
        $this->assertSame('Monday–Wednesday', departure_rule_day_label($saved));
    }
    public function testDispatcherSavesAmPmTimesAndMinuteIntervals(): void
    {
        $this->postRule(['time_from'=>'12:00 AM','time_to'=>'11:59 PM','wait_minutes'=>'25','wait_duration'=>'','round_number'=>'3']);
        $this->withUri('http://localhost/staff/departure-rules/store')->controller(ScopedDepartureRulesHarness::class)->execute('store');
        $saved=ScopedDepartureRulesHarness::$testDb->table('departure_rules')->where('round_number',3)->get()->getRowArray();
        $this->assertSame('00:00:00',$saved['time_from']);
        $this->assertSame('23:59:00',$saved['time_to']);
        $this->assertSame(25,(int)$saved['wait_minutes']);
    }

    public function testUpdatingDaysKeepsTheExplicitRuleRoundAndEditCheckboxes(): void
    {
        $this->postRule(['day_selection' => '1', 'days_of_week' => ['1', '4', '7']]);
        $result = $this->withUri('http://localhost/staff/departure-rules/update/4')
            ->controller(ScopedDepartureRulesHarness::class)->execute('update', 4);
        $this->assertSame(302, $result->response()->getStatusCode());
        $saved = ScopedDepartureRulesHarness::$testDb->table('departure_rules')->where('id', 4)->get()->getRowArray();
        $this->assertSame('1,4,7', $saved['days_of_week']);
        $this->assertSame(2, (int) $saved['round_number']);
        $this->assertSame('Monday, Thursday, Sunday', departure_rule_day_label($saved));
        $this->response = service('response', null, false);
        $result = $this->controller(ScopedDepartureRulesHarness::class)->execute('edit', 4);
        $document = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML($result->response()->getBody());
            $xpath = new \DOMXPath($document);
            $selected = $xpath->query('//input[@name="days_of_week[]" and @checked]');
            $this->assertSame(['1', '4', '7'], array_map(static fn($input) => $input->getAttribute('value'), iterator_to_array($selected)));
            $this->assertSame('2', $xpath->query('//select[@name="round_number"]/option[@selected]')->item(0)->getAttribute('value'));
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    public function testInvalidOrEmptyDaysAndBlankRoundDoNotSaveARule(): void
    {
        foreach ([[], ['8'], ['Monday'], '1'] as $days) {
            $this->postRule(['day_selection' => '1', 'days_of_week' => $days, 'round_number' => '3']);
            $this->response = service('response', null, false);
            $result = $this->controller(ScopedDepartureRulesHarness::class)->execute('store');
            $this->assertSame(302, $result->response()->getStatusCode());
            $this->assertSame(5, ScopedDepartureRulesHarness::$testDb->table('departure_rules')->countAllResults());
        }
        $this->postRule(['round_number' => '']);
        $this->response = service('response', null, false);
        $result = $this->controller(ScopedDepartureRulesHarness::class)->execute('store');
        $this->assertSame(302, $result->response()->getStatusCode());
        $this->assertSame(5, ScopedDepartureRulesHarness::$testDb->table('departure_rules')->countAllResults());
    }

    public function testOverlappingCheckedDaysAreRejectedButSeparateDaysCanShareARound(): void
    {
        ScopedDepartureRulesHarness::$testDb->table('departure_rules')->where('id', 4)->update(['days_of_week' => '1,2,3']);
        $this->postRule(['day_selection' => '1', 'days_of_week' => ['2', '4']]);
        $result = $this->controller(ScopedDepartureRulesHarness::class)->execute('store');
        $this->assertSame(302, $result->response()->getStatusCode());
        $this->assertSame(5, ScopedDepartureRulesHarness::$testDb->table('departure_rules')->countAllResults());
        $this->assertStringContainsString('overlaps', session()->getFlashdata('error'));
        $this->postRule(['day_selection' => '1', 'days_of_week' => ['4', '7']]);
        $this->response = service('response', null, false);
        $result = $this->controller(ScopedDepartureRulesHarness::class)->execute('store');
        $this->assertSame(302, $result->response()->getStatusCode());
        $this->assertSame(6, ScopedDepartureRulesHarness::$testDb->table('departure_rules')->countAllResults());
    }

    public function testUpdatingAMissingRuleReturns404(): void
    {
        $result = $this->withUri('http://localhost/staff/departure-rules/update/99')
            ->controller(ScopedDepartureRulesHarness::class)->execute('update', 99);
        $this->assertSame(404, $result->response()->getStatusCode());
    }

    public function testFirstRoundCanBeCorrectedWithoutConflictingWithAnotherDestination(): void
    {
        ScopedDepartureRulesHarness::$assignedIds = [1,2,3];
        ScopedDepartureRulesHarness::$testDb->table('departure_rules')->where('id',1)->update(['round_number'=>1]);
        $this->postRule(['route_id'=>'3', 'round_number'=>'1']);
        $result = $this->controller(ScopedDepartureRulesHarness::class)->execute('update',3);
        $this->assertSame(302,$result->response()->getStatusCode());
        $saved = ScopedDepartureRulesHarness::$testDb->table('departure_rules')->where('id',3)->get()->getRowArray();
        $this->assertSame(1,(int)$saved['round_number']);
        $this->assertSame(3,(int)$saved['route_id']);
        $this->assertSame([[1,3]],ScopedDepartureRulesHarness::$recalculations);
    }

    public function testMovingAnExistingRuleToANewDestinationRoundPreservesItsNumberAndExistingRules(): void
    {
        ScopedDepartureRulesHarness::$assignedIds = [1, 2, 3];
        ScopedDepartureRulesHarness::$testDb->table('departure_rules')->whereNotIn('id', [1, 3])->delete();
        ScopedDepartureRulesHarness::$testDb->table('departure_rules')->where('id', 1)->update(['round_number' => 1]);
        foreach (['staff', 'admin', 'superadmin'] as $role) {
            session()->set('role', $role);
            ScopedDepartureRulesHarness::$testDb->table('departure_rules')->where('id', 3)->update(['route_id' => 3, 'round_number' => 1]);
            $this->postRule(['route_id' => '1', 'round_number' => '2', 'day_selection' => '1', 'days_of_week' => ['1', '2', '3', '4', '5', '6', '7']]);
            $this->response = service('response', null, false);
            $result = $this->controller(ScopedDepartureRulesHarness::class)->execute('update', 3);
            $this->assertSame(302, $result->response()->getStatusCode());
            $moved = ScopedDepartureRulesHarness::$testDb->table('departure_rules')->where('id', 3)->get()->getRowArray();
            $this->assertSame(1, (int) $moved['route_id']);
            $this->assertSame(2, (int) $moved['round_number']);
            $this->assertSame(25, (int) $moved['wait_minutes']);
            $this->assertSame(range(1, 7), departure_rule_days($moved));
            $existing = ScopedDepartureRulesHarness::$testDb->table('departure_rules')->where('id', 1)->get()->getRowArray();
            $this->assertSame(1, (int) $existing['round_number']);
            $this->assertSame(20, (int) $existing['wait_minutes']);
            $this->assertSame(2, ScopedDepartureRulesHarness::$testDb->table('departure_rules')->countAllResults());
        }
    }

    public function testSavingAFirstDestinationRuleAlwaysStartsAtRoundOne(): void
    {
        ScopedDepartureRulesHarness::$assignedIds = [1, 2, 3];
        ScopedDepartureRulesHarness::$testDb->table('departure_rules')->where('id', 3)->delete();
        $this->postRule(['route_id' => '3', 'round_number' => '2']);
        $this->controller(ScopedDepartureRulesHarness::class)->execute('store');
        $saved = ScopedDepartureRulesHarness::$testDb->table('departure_rules')->where('route_id', 3)->get()->getRowArray();
        $this->assertSame(1, (int) $saved['round_number']);
        $this->assertSame(25, (int) $saved['wait_minutes']);
        $other = ScopedDepartureRulesHarness::$testDb->table('departure_rules')->where('id', 1)->get()->getRowArray();
        $this->assertSame(2, (int) $other['round_number']);
    }

    public function testEditingALoneDestinationRuleCorrectsItsStartingNumber(): void
    {
        ScopedDepartureRulesHarness::$assignedIds = [1, 2, 3];
        $this->postRule(['route_id' => '3', 'round_number' => '2']);
        $this->controller(ScopedDepartureRulesHarness::class)->execute('update', 3);
        $saved = ScopedDepartureRulesHarness::$testDb->table('departure_rules')->where('id', 3)->get()->getRowArray();
        $this->assertSame(1, (int) $saved['round_number']);
        $this->assertSame(25, (int) $saved['wait_minutes']);
    }
}
