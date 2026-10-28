<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;
use Throwable;

/** Enforce the four application references without changing existing records. */
class EnforceLogicalReferences extends Migration
{
    private const REFERENCES = [
        ['dispatch_rounds', 'terminal_id', 'terminals', 'id', 'CASCADE', 'fk_dispatch_rounds_terminal'],
        ['password_reset_tokens', 'user_id', 'users', 'id', 'CASCADE', 'fk_password_reset_tokens_user'],
        ['routes', 'vehicle_type', 'vehicle_types', 'slug', 'RESTRICT', 'fk_routes_vehicle_type'],
        ['vehicles', 'type', 'vehicle_types', 'slug', 'RESTRICT', 'fk_vehicles_type'],
    ];

    public function up()
    {
        $this->atomic(function (): void {
            $this->lockTables();
            $unmatched = [];
            foreach (self::REFERENCES as [$table, $column, $parent, $parentColumn]) {
                $count = (int) $this->query(
                    'SELECT COUNT(*) AS total FROM ' . $this->table($table) . ' c LEFT JOIN '
                    . $this->table($parent) . ' p ON p.' . $this->identifier($parentColumn)
                    . ' = c.' . $this->identifier($column)
                    . ' WHERE c.' . $this->identifier($column) . ' IS NOT NULL'
                    . ' AND p.' . $this->identifier($parentColumn) . ' IS NULL'
                )->getRowArray()['total'];
                if ($count > 0) {
                    $unmatched[] = "{$table}.{$column}: {$count} unmatched reference(s)";
                }
            }
            if ($unmatched !== []) {
                throw new RuntimeException('Cannot enforce references: ' . implode('; ', $unmatched)
                    . '. Correct the references and rerun the migration. No records were changed.');
            }

            // A type slug may be renamed or removed. Inserts must supply their actual type.
            $this->query('ALTER TABLE ' . $this->table('routes') . ' ALTER COLUMN vehicle_type DROP DEFAULT');
            foreach (self::REFERENCES as [$table, $column, $parent, $parentColumn, $onDelete, $name]) {
                $existing = $this->matchingConstraint($table, $column, $parent, $parentColumn, $onDelete, $name);
                if ($existing === null) {
                    $this->query('ALTER TABLE ' . $this->table($table) . ' ADD CONSTRAINT '
                        . $this->identifier($name) . ' FOREIGN KEY (' . $this->identifier($column) . ') REFERENCES '
                        . $this->table($parent) . ' (' . $this->identifier($parentColumn) . ')'
                        . ' ON DELETE ' . $onDelete . ' ON UPDATE CASCADE');
                } else {
                    $this->query('ALTER TABLE ' . $this->table($table) . ' VALIDATE CONSTRAINT ' . $this->identifier($existing));
                }
                $this->ensureLeadingIndex($table, $column);
            }
        });
    }

    public function down()
    {
        $this->atomic(function (): void {
            $this->lockTables();
            foreach (self::REFERENCES as [$table, $column, , , , $name]) {
                $this->query('ALTER TABLE ' . $this->table($table) . ' DROP CONSTRAINT IF EXISTS ' . $this->identifier($name));
                // Other indexes, including pre-existing composite indexes, are retained.
                $schema = $this->query('SELECT n.nspname FROM pg_class c JOIN pg_namespace n ON n.oid = c.relnamespace'
                    . ' WHERE c.oid = to_regclass(?)', [$this->table($table)])->getRowArray()['nspname'];
                $this->query('DROP INDEX IF EXISTS ' . $this->identifier($schema) . '.' . $this->identifier($this->indexName($table, $column)));
            }
            $this->query('ALTER TABLE ' . $this->table('routes') . " ALTER COLUMN vehicle_type SET DEFAULT 'van'");
        });
    }

    private function matchingConstraint(string $table, string $column, string $parent, string $parentColumn, string $onDelete, string $name): ?string
    {
        $rows = $this->query(
            "SELECT c.conname, c.confrelid = to_regclass(?) AS parent_matches, c.confdeltype, c.confupdtype,
                ARRAY(SELECT a.attname::text FROM unnest(c.conkey) WITH ORDINALITY k(num, ord)
                      JOIN pg_attribute a ON a.attrelid = c.conrelid AND a.attnum = k.num ORDER BY k.ord)::text AS columns,
                ARRAY(SELECT a.attname::text FROM unnest(c.confkey) WITH ORDINALITY k(num, ord)
                      JOIN pg_attribute a ON a.attrelid = c.confrelid AND a.attnum = k.num ORDER BY k.ord)::text AS parent_columns
             FROM pg_constraint c WHERE c.conrelid = to_regclass(?) AND c.contype = 'f'",
            [$this->table($parent), $this->table($table)]
        )->getResultArray();
        $matching = null;
        foreach ($rows as $row) {
            if ($row['conname'] !== $name && $row['columns'] !== '{' . $column . '}') {
                continue;
            }
            if ($row['columns'] !== '{' . $column . '}' || !in_array($row['parent_matches'], [true, 't', '1', 1], true)
                || $row['parent_columns'] !== '{' . $parentColumn . '}' || $row['confupdtype'] !== 'c'
                || $row['confdeltype'] !== ($onDelete === 'CASCADE' ? 'c' : 'r')) {
                throw new RuntimeException("Existing foreign key {$row['conname']} on {$table} has a different definition. Review it before rerunning this migration.");
            }
            $matching = $row['conname'];
        }
        return $matching;
    }

    private function ensureLeadingIndex(string $table, string $column): void
    {
        $row = $this->query(
            'SELECT 1 FROM pg_index i JOIN pg_attribute a ON a.attrelid = i.indrelid AND a.attnum = i.indkey[0]'
            . ' WHERE i.indrelid = to_regclass(?) AND a.attname = ? AND i.indisvalid AND i.indisready'
            . ' AND i.indpred IS NULL AND i.indexprs IS NULL LIMIT 1',
            [$this->table($table), $column]
        )->getRowArray();
        if (!$row) {
            $this->query('CREATE INDEX ' . $this->identifier($this->indexName($table, $column))
                . ' ON ' . $this->table($table) . ' (' . $this->identifier($column) . ')');
        }
    }

    private function lockTables(): void
    {
        // Prevent inserts/deletes between the preflight and constraint creation.
        $tables = ['dispatch_rounds', 'password_reset_tokens', 'routes', 'terminals', 'users', 'vehicle_types', 'vehicles'];
        $this->query("SET LOCAL lock_timeout = '10s'");
        $this->query('LOCK TABLE ' . implode(', ', array_map($this->table(...), $tables)) . ' IN SHARE ROW EXCLUSIVE MODE');
    }

    private function atomic(callable $operation): void
    {
        if ($this->db->DBDriver !== 'Postgre') {
            throw new RuntimeException('EnforceLogicalReferences requires PostgreSQL transactional DDL. No changes were made.');
        }
        if ($this->db->transDepth > 0) {
            throw new RuntimeException('Run EnforceLogicalReferences outside an existing transaction so its schema changes can roll back atomically.');
        }
        if (!$this->db->transBegin()) {
            throw new RuntimeException('Could not start the reference-constraint migration transaction.');
        }
        try {
            $operation();
            if (!$this->db->transStatus() || !$this->db->transCommit()) {
                throw new RuntimeException('Could not commit the reference-constraint migration.');
            }
        } catch (Throwable $error) {
            if ($this->db->transDepth > 0) {
                $this->db->transRollback();
            }
            $this->db->resetTransStatus();
            throw $error;
        }
        $this->db->resetDataCache();
    }

    private function query(string $sql, array $binds = [])
    {
        $result = $this->db->query($sql, $binds);
        if ($result === false) {
            throw new RuntimeException('A reference-constraint schema operation failed; all migration changes were rolled back.');
        }
        return $result;
    }

    private function table(string $table): string
    {
        return $this->identifier($this->db->prefixTable($table));
    }

    private function identifier(string $name): string
    {
        return $this->db->escapeIdentifiers($name);
    }

    private function indexName(string $table, string $column): string
    {
        return $this->db->DBPrefix . 'idx_reference_' . $table . '_' . $column;
    }
}
