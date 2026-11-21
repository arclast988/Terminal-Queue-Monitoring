<?php

namespace Tests\Unit;

use App\Database\Migrations\RestoreSingleTerminalDepartureRules;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use PHPUnit\Framework\Attributes\DataProvider;

final class SingleTerminalDepartureRulesRepairTest extends CIUnitTestCase
{
    private array $connections = [];

    public static function databaseDrivers(): array
    {
        return ['sqlite' => ['SQLite3'], 'postgres' => ['Postgre']];
    }

    protected function tearDown(): void
    {
        foreach ($this->connections as $db) $db->close();
        parent::tearDown();
    }

    private function database(string $driver): BaseConnection
    {
        require_once APPPATH . 'Database/Migrations/2026-11-20-000000_RestoreSingleTerminalDepartureRules.php';
        if ($driver === 'Postgre' && config(Database::class)->tests['DBDriver'] !== 'Postgre') {
            $this->markTestSkipped('This case requires the configured PostgreSQL test database.');
        }
        $config = $driver === 'SQLite3'
            ? ['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true]
            : array_replace(config(Database::class)->tests, ['DBDriver' => 'Postgre', 'DBPrefix' => '', 'options' => '-c application_name=tq_rule_reference_test']);
        $db = Database::connect($config, false);
        $this->connections[] = $db;
        foreach ([
            'CREATE TEMPORARY TABLE terminals (id INTEGER PRIMARY KEY, name VARCHAR(100))',
            'CREATE TEMPORARY TABLE routes (id INTEGER PRIMARY KEY, terminal_id INTEGER, destination VARCHAR(100))',
            'CREATE TEMPORARY TABLE departure_rules (id INTEGER PRIMARY KEY, terminal_id INTEGER, route_id INTEGER,
                time_from VARCHAR(8), time_to VARCHAR(8), wait_minutes INTEGER, label VARCHAR(100),
                day_of_week INTEGER, days_of_week VARCHAR(20), round_number INTEGER, created_at VARCHAR(30), updated_at VARCHAR(30))',
        ] as $sql) $db->query($driver === 'SQLite3' ? str_replace('TEMPORARY ', '', $sql) : $sql);
        if ($driver === 'Postgre') {
            $db->schema = $db->query('SELECT pg_my_temp_schema()::regnamespace::text AS name')->getRowArray()['name'];
        }
        $db->table('terminals')->insert(['id' => 3, 'name' => 'VILLABA']);
        $db->table('routes')->insertBatch([
            ['id' => 10, 'terminal_id' => 3, 'destination' => 'ORMOC'],
            ['id' => 11, 'terminal_id' => 2, 'destination' => 'ISABEL'],
        ]);
        return $db;
    }

    private function rule(BaseConnection $db, int $id, ?int $terminalId, ?int $routeId = null, array $values = []): void
    {
        $db->table('departure_rules')->insert(array_replace([
            'id' => $id, 'terminal_id' => $terminalId, 'route_id' => $routeId,
            'time_from' => '05:00:00', 'time_to' => '12:00:00', 'wait_minutes' => 25,
            'label' => 'Custom schedule ' . $id, 'day_of_week' => null, 'days_of_week' => '1,3,5',
            'round_number' => 1, 'created_at' => '2026-11-19 08:00:00', 'updated_at' => '2026-11-19 09:00:00',
        ], $values));
    }

    private function rows(BaseConnection $db): array
    {
        return $db->table('departure_rules')->orderBy('id')->get()->getResultArray();
    }

    private function migrate(BaseConnection $db): void
    {
        (new RestoreSingleTerminalDepartureRules(Database::forge($db)))->up();
    }

    #[DataProvider('databaseDrivers')]
    public function testRepairsOnlyInvalidReferencesWithoutChangingSchedulesAndRerunsAreSafe(string $driver): void
    {
        $db = $this->database($driver);
        foreach ([1 => 1, 2 => 2, 3 => 0, 4 => null, 5 => 3] as $id => $terminalId) $this->rule($db, $id, $terminalId);
        $this->rule($db, 6, 1, 10);
        $this->rule($db, 7, 2, 11);
        $this->rule($db, 8, null, 99);
        $this->rule($db, 9, 0, 0);
        // Existing overlapping defaults are retained; round numbers are not compacted.
        $this->rule($db, 10, 1, null, ['wait_minutes' => 30, 'label' => 'Earlier default']);
        $before = $this->rows($db);
        $expected = $before;
        foreach ([0, 1, 2, 3, 5, 9] as $index) $expected[$index]['terminal_id'] = $before[4]['terminal_id'];
        $this->migrate($db);
        $this->assertSame($expected, $this->rows($db));
        $this->migrate($db);
        (new RestoreSingleTerminalDepartureRules(Database::forge($db)))->down();
        $this->assertSame($expected, $this->rows($db));
        $this->assertTrue($db->transStatus());
    }

    #[DataProvider('databaseDrivers')]
    public function testZeroOrMultipleActualTerminalsLeaveEveryRuleUntouched(string $driver): void
    {
        $db = $this->database($driver);
        $this->rule($db, 1, 99);
        $this->rule($db, 2, null, 10);
        $before = $this->rows($db);
        $db->table('terminals')->insert(['id' => 4, 'name' => 'OTHER TERMINAL']);
        $this->migrate($db);
        $this->assertSame($before, $this->rows($db));
        $db->table('terminals')->truncate();
        $this->migrate($db);
        $this->assertSame($before, $this->rows($db));
    }

    #[DataProvider('databaseDrivers')]
    public function testUniqueWindowCollisionsRetainEveryRuleAndAllowUnrelatedRepairs(string $driver): void
    {
        $db = $this->database($driver);
        $db->query('CREATE UNIQUE INDEX rule_window_key ON departure_rules (terminal_id, time_from, time_to)');
        $this->rule($db, 1, 3);
        $this->rule($db, 2, 1);
        $this->rule($db, 3, 2, null, ['time_from' => '12:00:00', 'time_to' => '18:00:00']);
        $this->rule($db, 4, 99, null, ['time_from' => '12:00:00', 'time_to' => '18:00:00']);
        $before = $this->rows($db);
        $expected = $before;
        $expected[2]['terminal_id'] = $before[0]['terminal_id'];
        $this->migrate($db);
        $this->assertSame($expected, $this->rows($db));
        $this->migrate($db);
        $this->assertSame($expected, $this->rows($db));
        $this->assertTrue($db->transStatus());
    }

    #[DataProvider('databaseDrivers')]
    public function testMissingRoutesTableOnlyRepairsTerminalDefaults(string $driver): void
    {
        $db = $this->database($driver);
        $this->rule($db, 1, null);
        $this->rule($db, 2, 99, 10);
        $db->query('DROP TABLE routes');
        $db->resetDataCache();
        $before = $this->rows($db);
        $this->migrate($db);
        $after = $this->rows($db);
        $this->assertSame(3, (int) $after[0]['terminal_id']);
        $this->assertSame($before[1], $after[1]);
    }

    #[DataProvider('databaseDrivers')]
    public function testLegacySchemaWithoutTerminalReferencesIsIgnored(string $driver): void
    {
        $db = $this->database($driver);
        $db->query('DROP TABLE departure_rules');
        $db->query('CREATE ' . ($driver === 'Postgre' ? 'TEMPORARY ' : '') . 'TABLE departure_rules (id INTEGER PRIMARY KEY, label VARCHAR(100))');
        $db->resetDataCache();
        $db->table('departure_rules')->insert(['id' => 1, 'label' => 'Legacy setting']);
        $before = $this->rows($db);
        $this->migrate($db);
        $this->assertSame($before, $this->rows($db));
    }

    #[DataProvider('databaseDrivers')]
    public function testExpressionUniqueKeysRequireReviewWithoutChangingRules(string $driver): void
    {
        $db = $this->database($driver);
        $db->query('CREATE UNIQUE INDEX expression_rule_key ON departure_rules ((terminal_id + 1), time_from)');
        $this->rule($db, 1, 99);
        $before = $this->rows($db);
        $this->migrate($db);
        $this->assertSame($before, $this->rows($db));
        $this->assertTrue($db->transStatus());
    }

    #[DataProvider('databaseDrivers')]
    public function testNullableRouteKeysPreserveOverlappingTerminalDefaults(string $driver): void
    {
        $db = $this->database($driver);
        $db->query('CREATE UNIQUE INDEX nullable_rule_key ON departure_rules (terminal_id, route_id, time_from, time_to)');
        $this->rule($db, 1, 3);
        $this->rule($db, 2, 99, null, ['wait_minutes' => 30]);
        $this->migrate($db);
        $this->assertSame([3, 3], array_map('intval', array_column($this->rows($db), 'terminal_id')));
        $this->assertSame([25, 30], array_map('intval', array_column($this->rows($db), 'wait_minutes')));
    }

    public function testFailureRollsBackEveryEarlierRepair(): void
    {
        $db = $this->database('SQLite3');
        $this->rule($db, 1, 1);
        $this->rule($db, 2, null);
        $db->query("CREATE TRIGGER reject_second_repair BEFORE UPDATE ON departure_rules
            WHEN OLD.id = 2 BEGIN SELECT RAISE(ABORT, 'simulated reference repair failure'); END");
        $before = $this->rows($db);
        try {
            $this->migrate($db);
            $this->fail('The simulated write failure must stop the migration.');
        } catch (\RuntimeException $error) {
            $this->assertStringContainsString('Could not restore a departure-rule terminal reference.', $error->getMessage());
        }
        $this->assertSame($before, $this->rows($db));
        $this->assertTrue($db->transStatus());
        $db->query('DROP TRIGGER reject_second_repair');
        $this->migrate($db);
        $this->assertSame([3, 3], array_map('intval', array_column($this->rows($db), 'terminal_id')));
    }
}
