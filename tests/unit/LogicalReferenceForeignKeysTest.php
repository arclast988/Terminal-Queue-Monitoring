<?php

namespace Tests\Unit;

use App\Database\Migrations\EnforceLogicalReferences;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use RuntimeException;

final class LogicalReferenceForeignKeysTest extends CIUnitTestCase
{
    private BaseConnection $referenceDb;
    private EnforceLogicalReferences $migration;

    protected function setUp(): void
    {
        parent::setUp();
        require_once APPPATH . 'Database/Migrations/2026-10-28-000000_EnforceLogicalReferences.php';
        $this->referenceDb = Database::connect(array_replace(config(Database::class)->tests, [
            'DBPrefix' => '', 'options' => '-c timezone=Asia/Manila -c application_name=tq_reference_fk_test',
        ]), false);
        foreach ([
            'CREATE TEMPORARY TABLE terminals (id INTEGER PRIMARY KEY)',
            'CREATE TEMPORARY TABLE users (id INTEGER PRIMARY KEY)',
            'CREATE TEMPORARY TABLE vehicle_types (id INTEGER PRIMARY KEY, slug VARCHAR(50) NOT NULL UNIQUE)',
            "CREATE TEMPORARY TABLE routes (id INTEGER PRIMARY KEY, vehicle_type VARCHAR(50) NOT NULL DEFAULT 'van',
                terminal_id INTEGER, CONSTRAINT existing_route_terminal FOREIGN KEY (terminal_id)
                REFERENCES terminals(id) ON UPDATE CASCADE ON DELETE SET NULL)",
            'CREATE TEMPORARY TABLE vehicles (id INTEGER PRIMARY KEY, type VARCHAR(50) NOT NULL)',
            'CREATE TEMPORARY TABLE password_reset_tokens (id INTEGER PRIMARY KEY, user_id INTEGER)',
            'CREATE TEMPORARY TABLE dispatch_rounds (id INTEGER PRIMARY KEY, terminal_id INTEGER NOT NULL,
                destination VARCHAR(100), UNIQUE (terminal_id, destination))',
        ] as $sql) {
            $this->referenceDb->query($sql);
        }
        $this->referenceDb->schema = (string) $this->referenceDb
            ->query('SELECT pg_my_temp_schema()::regnamespace::text AS name')->getRowArray()['name'];
        $this->referenceDb->table('terminals')->insert(['id' => 1]);
        $this->referenceDb->table('users')->insert(['id' => 1]);
        $this->referenceDb->table('vehicle_types')->insertBatch([['id' => 1, 'slug' => 'van'], ['id' => 2, 'slug' => 'bus']]);
        $this->referenceDb->table('routes')->insert(['id' => 10, 'terminal_id' => 1, 'vehicle_type' => 'van']);
        $this->referenceDb->table('vehicles')->insert(['id' => 20, 'type' => 'van']);
        $this->referenceDb->table('password_reset_tokens')->insertBatch([['id' => 30, 'user_id' => 1], ['id' => 31, 'user_id' => null]]);
        $this->referenceDb->table('dispatch_rounds')->insert(['id' => 40, 'terminal_id' => 1, 'destination' => 'ORMOC']);
        $this->migration = new EnforceLogicalReferences(Database::forge($this->referenceDb));
    }

    protected function tearDown(): void
    {
        $this->referenceDb->close();
        parent::tearDown();
    }

    public function testEveryReferenceRejectsUnknownParentsAndNullableTokenUsersRemainAllowed(): void
    {
        $this->migration->up();
        foreach ([['dispatch_rounds', 'terminal_id', 999], ['password_reset_tokens', 'user_id', 999],
            ['routes', 'vehicle_type', 'missing'], ['vehicles', 'type', 'missing']] as [$table, $column, $value]) {
            $this->assertRejected(fn () => $this->referenceDb->table($table)->update([$column => $value]));
        }
        $this->assertTrue($this->referenceDb->table('password_reset_tokens')->insert(['id' => 32, 'user_id' => null]));
        $this->assertSame(2, $this->referenceDb->table('password_reset_tokens')->where('user_id', null)->countAllResults());
        $this->assertSame([
            'fk_dispatch_rounds_terminal' => ['c', 'c'],
            'fk_password_reset_tokens_user' => ['c', 'c'],
            'fk_routes_vehicle_type' => ['r', 'c'],
            'fk_vehicles_type' => ['r', 'c'],
        ], $this->newConstraintActions());
    }

    public function testParentRenamesCascadeAndParentDeletionUsesTheIntendedRules(): void
    {
        $this->migration->up();
        $this->referenceDb->table('vehicle_types')->where('id', 1)->update(['slug' => 'passenger_van']);
        $this->assertSame('passenger_van', $this->row('routes', 10)['vehicle_type']);
        $this->assertSame('passenger_van', $this->row('vehicles', 20)['type']);
        $this->assertRejected(fn () => $this->referenceDb->table('vehicle_types')->where('id', 1)->delete());
        $this->assertNotNull($this->row('vehicles', 20));
        $this->assertNotNull($this->row('routes', 10));
        $this->assertNull($this->routeDefault());
        $this->assertRejected(fn () => $this->referenceDb->table('routes')->insert(['id' => 11]));
        $this->assertTrue($this->referenceDb->table('routes')->insert(['id' => 11, 'vehicle_type' => 'passenger_van']));

        $this->referenceDb->table('users')->where('id', 1)->update(['id' => 2]);
        $this->assertSame(2, (int) $this->row('password_reset_tokens', 30)['user_id']);
        $this->referenceDb->table('users')->where('id', 2)->delete();
        $this->assertNull($this->row('password_reset_tokens', 30));
        $this->assertNotNull($this->row('password_reset_tokens', 31));
        $this->referenceDb->table('terminals')->where('id', 1)->update(['id' => 2]);
        $this->assertSame(2, (int) $this->row('dispatch_rounds', 40)['terminal_id']);
        $this->referenceDb->table('terminals')->where('id', 2)->delete();
        $this->assertNull($this->row('dispatch_rounds', 40));
        $this->assertNotNull($this->row('routes', 10));
    }

    public function testPreflightReportsAllOrphansWithoutChangingRowsOrSchema(): void
    {
        foreach ([['dispatch_rounds', 'terminal_id', 999], ['password_reset_tokens', 'user_id', 999],
            ['routes', 'vehicle_type', 'legacy_type'], ['vehicles', 'type', 'old_van']] as [$table, $column, $value]) {
            $this->referenceDb->table($table)->update([$column => $value]);
        }
        $before = $this->snapshots();
        try {
            $this->migration->up();
            $this->fail('Unmatched references must stop the migration.');
        } catch (RuntimeException $error) {
            foreach (['dispatch_rounds.terminal_id: 1', 'password_reset_tokens.user_id: 2',
                'routes.vehicle_type: 1', 'vehicles.type: 1'] as $message) {
                $this->assertStringContainsString($message, $error->getMessage());
            }
        }
        $this->assertSame([], $this->newConstraintActions());
        $this->assertSame($before, $this->snapshots());
        $this->assertStringContainsString('van', $this->routeDefault());
        $this->assertSame(0, $this->ownIndexCount());
    }

    public function testSchemaFailureRollsBackEarlierConstraintsIndexesAndDefault(): void
    {
        $this->referenceDb->query("ALTER TABLE vehicles ADD CONSTRAINT fk_vehicles_type CHECK (type <> '')");
        $before = $this->snapshots();
        try {
            $this->migration->up();
            $this->fail('The conflicting constraint name must prevent installation.');
        } catch (RuntimeException $error) {
            $this->assertStringContainsString('rolled back', $error->getMessage());
        }
        $this->assertSame([], $this->newConstraintActions());
        $this->assertSame(0, $this->ownIndexCount());
        $this->assertStringContainsString('van', $this->routeDefault());
        $this->assertSame($before, $this->snapshots());
        $this->referenceDb->query('ALTER TABLE vehicles DROP CONSTRAINT fk_vehicles_type');
        $this->migration->up();
        $this->assertCount(4, $this->newConstraintActions());
    }

    public function testRerunAndRollbackPreservePreexistingConstraintsAndIndexes(): void
    {
        $this->referenceDb->query('CREATE INDEX existing_vehicle_type_index ON vehicles (type, id)');
        $this->migration->up();
        $this->migration->up();
        $this->assertCount(4, $this->newConstraintActions());
        // The dispatch composite unique key and existing vehicle index already cover their references.
        $this->assertSame(2, $this->ownIndexCount());
        $this->migration->down();
        $this->migration->down();
        $this->assertSame([], $this->newConstraintActions());
        $this->assertSame(0, $this->ownIndexCount());
        $this->assertStringContainsString('van', $this->routeDefault());
        $this->assertTrue((bool) $this->referenceDb->query("SELECT 1 FROM pg_constraint
            WHERE conrelid = 'routes'::regclass AND conname = 'existing_route_terminal'")->getRowArray());
        $this->assertTrue((bool) $this->referenceDb->query("SELECT 1 FROM pg_indexes WHERE schemaname = ?
            AND indexname = 'existing_vehicle_type_index'", [$this->referenceDb->schema])->getRowArray());
        $this->assertTrue($this->referenceDb->table('vehicles')->update(['type' => 'unconstrained_after_rollback']));
    }

    public function testEquivalentPreexistingForeignKeyIsReusedAndSurvivesRollback(): void
    {
        $this->referenceDb->query('ALTER TABLE vehicles ADD CONSTRAINT prior_vehicle_type_link
            FOREIGN KEY (type) REFERENCES vehicle_types(slug) ON DELETE RESTRICT ON UPDATE CASCADE');
        $this->migration->up();
        $this->assertCount(3, $this->newConstraintActions());
        $this->migration->down();
        $this->assertTrue((bool) $this->referenceDb->query("SELECT 1 FROM pg_constraint
            WHERE conrelid = 'vehicles'::regclass AND conname = 'prior_vehicle_type_link'")->getRowArray());
        $this->assertRejected(fn () => $this->referenceDb->table('vehicles')->update(['type' => 'missing']));
    }

    private function assertRejected(callable $write): void
    {
        try {
            $write();
            $this->fail('The database should reject this invalid reference or parent deletion.');
        } catch (DatabaseException $error) {
            $this->assertNotSame('', $error->getMessage());
        }
    }

    private function row(string $table, int $id): ?array
    {
        return $this->referenceDb->table($table)->where('id', $id)->get()->getRowArray();
    }

    private function routeDefault(): ?string
    {
        return $this->referenceDb->query("SELECT pg_get_expr(d.adbin, d.adrelid) AS value
            FROM pg_attribute a LEFT JOIN pg_attrdef d ON d.adrelid = a.attrelid AND d.adnum = a.attnum
            WHERE a.attrelid = 'routes'::regclass AND a.attname = 'vehicle_type'")->getRowArray()['value'];
    }

    private function newConstraintActions(): array
    {
        $rows = $this->referenceDb->query("SELECT conname, confdeltype, confupdtype FROM pg_constraint
            WHERE connamespace = ?::regnamespace AND contype = 'f' AND conname IN
            ('fk_dispatch_rounds_terminal', 'fk_password_reset_tokens_user', 'fk_routes_vehicle_type', 'fk_vehicles_type')
            ORDER BY conname", [$this->referenceDb->schema])->getResultArray();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['conname']] = [$row['confdeltype'], $row['confupdtype']];
        }
        return $result;
    }

    private function ownIndexCount(): int
    {
        return (int) $this->referenceDb->query("SELECT COUNT(*) AS total FROM pg_indexes
            WHERE schemaname = ? AND indexname LIKE 'idx_reference_%'", [$this->referenceDb->schema])->getRowArray()['total'];
    }

    private function snapshots(): array
    {
        $rows = [];
        foreach (['dispatch_rounds', 'password_reset_tokens', 'routes', 'vehicles'] as $table) {
            $rows[$table] = $this->referenceDb->table($table)->orderBy('id')->get()->getResultArray();
        }
        return $rows;
    }
}
