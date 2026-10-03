<?php

namespace Tests\Unit;

use App\Controllers\Admin\DepartureRules;
use App\Models\DepartureRuleModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use Config\Database;

class ScopedDepartureRulesHarness extends DepartureRules
{
    public static BaseConnection $testDb;

    public function __construct()
    {
        $this->ruleModel = new DepartureRuleModel(self::$testDb);
    }

    protected function assignedRouteIds(): ?array
    {
        return session()->get('role') === 'staff' ? [1, 2] : null;
    }
}

final class DepartureRuleAccessTest extends CIUnitTestCase
{
    use ControllerTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        ScopedDepartureRulesHarness::$testDb = Database::connect([
            'DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true,
        ], false);
        ScopedDepartureRulesHarness::$testDb->query('CREATE TABLE departure_rules (id INTEGER PRIMARY KEY, terminal_id INTEGER, route_id INTEGER)');
        ScopedDepartureRulesHarness::$testDb->table('departure_rules')->insertBatch([
            ['id' => 1, 'terminal_id' => 1, 'route_id' => 1],
            ['id' => 2, 'terminal_id' => 1, 'route_id' => 2],
            ['id' => 3, 'terminal_id' => 1, 'route_id' => 3],
            ['id' => 4, 'terminal_id' => 1, 'route_id' => null],
        ]);
        session()->set(['isLoggedIn' => true, 'id' => 123, 'role' => 'staff']);
    }

    protected function tearDown(): void
    {
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
        $this->assertFalse($method->invoke($controller, ['route_id' => null]));
        session()->set('role', 'admin');
        $this->assertTrue($method->invoke($controller, ['route_id' => null]));
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

    public function testDeletingATerminalDefaultReturns403AndPreservesIt(): void
    {
        $result = $this->withUri('http://localhost/staff/departure-rules/delete/4')
            ->controller(ScopedDepartureRulesHarness::class)->execute('delete', 4);
        $this->assertSame(403, $result->response()->getStatusCode());
        $this->assertSame(1, ScopedDepartureRulesHarness::$testDb->table('departure_rules')->where('id', 4)->countAllResults());
    }

    public function testUpdatingAMissingRuleReturns404(): void
    {
        $result = $this->withUri('http://localhost/staff/departure-rules/update/99')
            ->controller(ScopedDepartureRulesHarness::class)->execute('update', 99);
        $this->assertSame(404, $result->response()->getStatusCode());
    }
}
