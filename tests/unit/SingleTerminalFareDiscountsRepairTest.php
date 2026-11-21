<?php

namespace Tests\Unit;

use App\Database\Migrations\RestoreSingleTerminalFareDiscounts;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use PHPUnit\Framework\Attributes\DataProvider;

final class SingleTerminalFareDiscountsRepairTest extends CIUnitTestCase
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
        require_once APPPATH . 'Database/Migrations/2026-11-21-000000_RestoreSingleTerminalFareDiscounts.php';
        if ($driver === 'Postgre' && config(Database::class)->tests['DBDriver'] !== 'Postgre') {
            $this->markTestSkipped('This case requires the configured PostgreSQL test database.');
        }
        $config = $driver === 'SQLite3'
            ? ['DBDriver' => 'SQLite3', 'database' => ':memory:', 'DBPrefix' => '', 'DBDebug' => true, 'foreignKeys' => true]
            : array_replace(config(Database::class)->tests, ['DBDriver' => 'Postgre', 'DBPrefix' => '', 'options' => '-c application_name=tq_fare_discount_reference_test']);
        $db = Database::connect($config, false);
        $this->connections[] = $db;
        foreach ([
            'CREATE TEMPORARY TABLE terminals (id INTEGER PRIMARY KEY, name VARCHAR(100))',
            'CREATE TEMPORARY TABLE fare_discounts (id INTEGER PRIMARY KEY, terminal_id INTEGER, type VARCHAR(50),
                label VARCHAR(100), discount_percent DECIMAL(5,2), is_active INTEGER, created_at VARCHAR(30), updated_at VARCHAR(30),
                UNIQUE (terminal_id, type))',
            'CREATE TEMPORARY TABLE fares (id INTEGER PRIMARY KEY, route_id INTEGER, fare_discount_id INTEGER REFERENCES fare_discounts(id), amount DECIMAL(10,2))',
        ] as $sql) $db->query($driver === 'SQLite3' ? str_replace('TEMPORARY ', '', $sql) : $sql);
        if ($driver === 'Postgre') {
            $db->schema = $db->query('SELECT pg_my_temp_schema()::regnamespace::text AS name')->getRowArray()['name'];
        }
        $db->table('terminals')->insert(['id' => 3, 'name' => 'VILLABA']);
        return $db;
    }

    private function discount(BaseConnection $db, int $id, ?int $terminalId, string $type, array $values = []): void
    {
        $db->table('fare_discounts')->insert(array_replace([
            'id' => $id, 'terminal_id' => $terminalId, 'type' => $type, 'label' => 'Existing ' . $type . ' discount',
            'discount_percent' => 20, 'is_active' => 1,
            'created_at' => '2026-11-19 08:00:00', 'updated_at' => '2026-11-19 09:00:00',
        ], $values));
    }

    private function snapshot(BaseConnection $db): array
    {
        return [
            'discounts' => $db->table('fare_discounts')->orderBy('id')->get()->getResultArray(),
            'fares' => $db->table('fares')->orderBy('id')->get()->getResultArray(),
        ];
    }

    private function migrate(BaseConnection $db): void
    {
        (new RestoreSingleTerminalFareDiscounts(Database::forge($db)))->up();
    }

    #[DataProvider('databaseDrivers')]
    public function testRestoresOnlyReferencesPreservingEveryPolicyAndStoredFareAndRerunsSafely(string $driver): void
    {
        $db = $this->database($driver);
        foreach ([[1, 1, 'pwd', 20, 1], [2, 1, 'senior_citizen', 20, 1], [3, null, 'student', 15, 1],
            [4, 3, 'regular', 0, 1], [5, 0, 'afternoon', 50, 1], [6, 99, 'inactive', 37.5, 0]] as [$id, $terminal, $type, $percent, $active]) {
            $this->discount($db, $id, $terminal, $type, ['discount_percent' => $percent, 'is_active' => $active]);
            $db->table('fares')->insert(['id' => $id, 'route_id' => 100 + $id, 'fare_discount_id' => $id, 'amount' => 71.25 + $id]);
        }
        $before = $this->snapshot($db);
        $expected = $before;
        foreach ($expected['discounts'] as &$discount) $discount['terminal_id'] = $before['discounts'][3]['terminal_id'];
        unset($discount);
        $this->migrate($db);
        $this->assertSame($expected, $this->snapshot($db));
        $this->migrate($db);
        (new RestoreSingleTerminalFareDiscounts(Database::forge($db)))->down();
        $this->assertSame($expected, $this->snapshot($db));
        $this->assertTrue($db->transStatus());
    }

    #[DataProvider('databaseDrivers')]
    public function testNoOrMultipleActualTerminalsLeaveAllPoliciesAndFareQuotesUntouched(string $driver): void
    {
        $db = $this->database($driver);
        $this->discount($db, 1, 1, 'pwd');
        $this->discount($db, 2, null, 'student');
        $before = $this->snapshot($db);
        $db->table('terminals')->insert(['id' => 4, 'name' => 'OTHER TERMINAL']);
        $this->migrate($db);
        $this->assertSame($before, $this->snapshot($db));
        $db->table('terminals')->truncate();
        $this->migrate($db);
        $this->assertSame($before, $this->snapshot($db));
    }

    #[DataProvider('databaseDrivers')]
    public function testConflictingPoliciesRetainTheirIdentityRatesAndFareReferences(string $driver): void
    {
        $db = $this->database($driver);
        $this->discount($db, 1, 1, 'pwd', ['discount_percent' => 20]);
        $this->discount($db, 2, 3, 'pwd', ['discount_percent' => 10, 'is_active' => 0]);
        $this->discount($db, 3, 1, 'student', ['discount_percent' => 15]);
        $this->discount($db, 4, null, 'student', ['discount_percent' => 25]);
        foreach ([1, 2, 3, 4] as $id) $db->table('fares')->insert(['id' => $id, 'route_id' => 101, 'fare_discount_id' => $id, 'amount' => 90 + $id]);
        $before = $this->snapshot($db);
        $expected = $before;
        $expected['discounts'][2]['terminal_id'] = $before['discounts'][1]['terminal_id'];
        $this->migrate($db);
        $this->assertSame($expected, $this->snapshot($db));
        $this->migrate($db);
        $this->assertSame($expected, $this->snapshot($db));
        $this->assertTrue($db->transStatus());
    }

    #[DataProvider('databaseDrivers')]
    public function testLegacyPolicySchemaWithoutTerminalReferencesIsIgnored(string $driver): void
    {
        $db = $this->database($driver);
        $db->query('DROP TABLE fares');
        $db->query('DROP TABLE fare_discounts');
        $db->query('CREATE ' . ($driver === 'Postgre' ? 'TEMPORARY ' : '') . 'TABLE fare_discounts (id INTEGER PRIMARY KEY, type VARCHAR(50), discount_percent DECIMAL(5,2))');
        $db->resetDataCache();
        $db->table('fare_discounts')->insert(['id' => 1, 'type' => 'pwd', 'discount_percent' => 20]);
        $before = $db->table('fare_discounts')->get()->getResultArray();
        $this->migrate($db);
        $this->assertSame($before, $db->table('fare_discounts')->get()->getResultArray());
    }

    public function testFailureRollsBackEveryEarlierPolicyRepairAndCanBeRetried(): void
    {
        $db = $this->database('SQLite3');
        $this->discount($db, 1, 1, 'pwd');
        $this->discount($db, 2, 1, 'student');
        $db->table('fares')->insert(['id' => 1, 'route_id' => 101, 'fare_discount_id' => 1, 'amount' => 77.5]);
        $db->query("CREATE TRIGGER reject_second_policy_repair BEFORE UPDATE ON fare_discounts
            WHEN OLD.id = 2 BEGIN SELECT RAISE(ABORT, 'simulated policy repair failure'); END");
        $before = $this->snapshot($db);
        try {
            $this->migrate($db);
            $this->fail('The simulated write failure must stop the migration.');
        } catch (\RuntimeException $error) {
            $this->assertStringContainsString('Could not restore a fare-discount terminal reference.', $error->getMessage());
        }
        $this->assertSame($before, $this->snapshot($db));
        $this->assertTrue($db->transStatus());
        $db->query('DROP TRIGGER reject_second_policy_repair');
        $this->migrate($db);
        $after = $this->snapshot($db);
        $this->assertSame([3, 3], array_map('intval', array_column($after['discounts'], 'terminal_id')));
        $this->assertSame($before['fares'], $after['fares']);
    }
}
