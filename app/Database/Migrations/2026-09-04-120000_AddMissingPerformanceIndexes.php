<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds missing supporting indexes for high-traffic filters.
 *
 * Safe and idempotent: creates indexes only if they do not already exist.
 */
class AddMissingPerformanceIndexes extends Migration
{
    public function up()
    {
        $this->ensureIndex('audit_logs', 'idx_audit_logs_action', ['action']);
        $this->ensureIndex('user_routes', 'idx_user_routes_user', ['user_id']);
        $this->ensureIndex('user_routes', 'idx_user_routes_route', ['route_id']);
        $this->ensureIndex('departure_rules', 'idx_departure_rules_terminal_route_time', ['terminal_id', 'route_id', 'time_from', 'time_to']);
        $this->ensureIndex('fares', 'idx_fares_route_discount', ['route_id', 'fare_discount_id']);
    }

    public function down()
    {
        $this->dropIndex('idx_audit_logs_action');
        $this->dropIndex('idx_user_routes_user');
        $this->dropIndex('idx_user_routes_route');
        $this->dropIndex('idx_departure_rules_terminal_route_time');
        $this->dropIndex('idx_fares_route_discount');
    }

    private function ensureIndex(string $table, string $indexName, array $columns): void
    {
        $db = \Config\Database::connect();

        try {
            if ($this->indexExists($db, $indexName)) {
                return;
            }

            $columnList = '"' . implode('", "', $columns) . '"';
            $db->query("CREATE INDEX \"{$indexName}\" ON \"{$table}\" ({$columnList})");
        } catch (\Throwable $e) {
            log_message('error', "Failed to ensure index {$indexName} on {$table}: " . $e->getMessage());
        }
    }

    private function dropIndex(string $indexName): void
    {
        $db = \Config\Database::connect();

        try {
            if (!$this->indexExists($db, $indexName)) {
                return;
            }

            $db->query("DROP INDEX \"{$indexName}\"");
        } catch (\Throwable $e) {
            log_message('error', "Failed to drop index {$indexName}: " . $e->getMessage());
        }
    }

    private function indexExists(\CodeIgniter\Database\BaseConnection $db, string $indexName): bool
    {
        // PostgreSQL catalog; fall back to generic check for SQLite/MySQL test DBs.
        try {
            $row = $db->query(
                'SELECT 1 FROM pg_indexes WHERE schemaname = current_schema() AND indexname = ?',
                [$indexName]
            )->getRowArray();

            return (bool) $row;
        } catch (\Throwable $e) {
            try {
                $tables = $db->listTables();
            } catch (\Throwable $e2) {
                return false;
            }

            return false;
        }
    }
}
