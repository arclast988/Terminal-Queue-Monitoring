<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds high-traffic composite indexes to optimize read/write performance
 * and reduce query latency under high concurrent load.
 *
 * Safe and idempotent: creates indexes only if they do not already exist.
 */
class AddPerformanceCompositeIndexes extends Migration
{
    public function up()
    {
        // Composite indexes on queue table
        $this->ensureIndex('queue', 'idx_queue_route_status_pos', ['route_id', 'status', 'position']);
        $this->ensureIndex('queue', 'idx_queue_vehicle_status', ['vehicle_id', 'status']);
        $this->ensureIndex('queue', 'idx_queue_status_departure', ['status', 'departure_time']);
        $this->ensureIndex('queue', 'idx_queue_status_arrival', ['status', 'arrival_time']);

        // Composite indexes on routes table
        $this->ensureIndex('routes', 'idx_routes_terminal_dest', ['terminal_id', 'destination']);

        // Composite index on fare_discounts table
        $this->ensureIndex('fare_discounts', 'idx_fare_discounts_terminal_active', ['terminal_id', 'is_active']);

        // Composite index on announcements table
        $this->ensureIndex('announcements', 'idx_announcements_active_sort', ['is_active', 'sort_order']);
    }

    public function down()
    {
        $this->dropIndex('idx_queue_route_status_pos');
        $this->dropIndex('idx_queue_vehicle_status');
        $this->dropIndex('idx_queue_status_departure');
        $this->dropIndex('idx_queue_status_arrival');

        $this->dropIndex('idx_routes_terminal_dest');
        $this->dropIndex('idx_fare_discounts_terminal_active');
        $this->dropIndex('idx_announcements_active_sort');
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
        $row = $db->query(
            'SELECT 1 FROM pg_indexes WHERE schemaname = current_schema() AND indexname = ?',
            [$indexName]
        )->getRowArray();

        return (bool) $row;
    }
}
