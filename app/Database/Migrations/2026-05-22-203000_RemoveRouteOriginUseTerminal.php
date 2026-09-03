<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Migration;

class RemoveRouteOriginUseTerminal extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if (!$db->tableExists('routes')) {
            return;
        }

        $this->dropIndexIfExists($db, 'unique_terminal_destination_vehicle');

        if ($db->fieldExists('origin', 'routes')) {
            $db->query('ALTER TABLE routes DROP COLUMN origin');
        }

        // PostgreSQL doesn't support column reordering (MODIFY ... AFTER),
        // so we skip the reorder statements that were in the MySQL version.
        // Column order doesn't affect application behaviour.

        $this->addUniqueIndexIfMissing($db, 'routes', 'unique_terminal_destination_vehicle', ['terminal_id', 'destination', 'vehicle_type']);
    }

    public function down()
    {
        $db = \Config\Database::connect();

        if (!$db->tableExists('routes')) {
            return;
        }

        $this->dropIndexIfExists($db, 'unique_terminal_destination_vehicle');

        if (!$db->fieldExists('origin', 'routes')) {
            $db->query("ALTER TABLE routes ADD COLUMN origin VARCHAR(100) NOT NULL DEFAULT ''");
        }

        if ($db->tableExists('terminals')) {
            $db->query("
                UPDATE routes
                SET origin = UPPER(TRIM(terminals.name))
                FROM terminals
                WHERE terminals.id = routes.terminal_id
            ");
        }
    }

    private function addUniqueIndexIfMissing(BaseConnection $db, string $table, string $indexName, array $columns): void
    {
        if ($this->indexExists($db, $indexName)) {
            return;
        }

        $columnList = '"' . implode('", "', $columns) . '"';
        $db->query("ALTER TABLE \"{$table}\" ADD CONSTRAINT \"{$indexName}\" UNIQUE ({$columnList})");
    }

    private function dropIndexIfExists(BaseConnection $db, string $indexName): void
    {
        if (!$this->indexExists($db, $indexName)) {
            return;
        }

        try {
            $db->query("DROP INDEX \"{$indexName}\"");
        } catch (\Throwable $e) {
            $db->query("ALTER TABLE \"routes\" DROP CONSTRAINT IF EXISTS \"{$indexName}\"");
        }
    }

    private function indexExists(BaseConnection $db, string $indexName): bool
    {
        $indexRow = $db->query(
            'SELECT 1 FROM pg_indexes WHERE schemaname = current_schema() AND indexname = ?',
            [$indexName]
        )->getRowArray();

        if ($indexRow) {
            return true;
        }

        $constraintRow = $db->query(
            'SELECT 1 FROM pg_constraint WHERE conname = ? AND connamespace = current_schema()::regnamespace',
            [$indexName]
        )->getRowArray();

        return (bool) $constraintRow;
    }
}
