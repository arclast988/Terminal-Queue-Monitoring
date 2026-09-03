<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds performance indexes to high-traffic columns.
 *
 * The application repeatedly executes queries like:
 *   - SELECT ... WHERE status IN ('waiting','boarding') ORDER BY position
 *   - SELECT ... WHERE status = 'departed' ORDER BY departure_time DESC
 *   - SELECT ... FROM audit_logs ORDER BY timestamp DESC
 *
 * Without these indexes the queries do full table scans, which
 * becomes noticeable as the queue and log tables grow.
 *
 * Idempotent: each index is created only if it does not already
 * exist, so the migration is safe to re-run.
 */
class AddQueueIndexes extends Migration
{
    public function up()
    {
        $this->ensureIndex('queue', 'idx_queue_status', ['status']);
        $this->ensureIndex('queue', 'idx_queue_status_position', ['status', 'position']);
        $this->ensureIndex('queue', 'idx_queue_departure_time', ['departure_time']);
        $this->ensureIndex('audit_logs',  'idx_audit_logs_timestamp', ['timestamp']);
    }

    public function down()
    {
        $this->dropIndex('idx_queue_status');
        $this->dropIndex('idx_queue_status_position');
        $this->dropIndex('idx_queue_departure_time');
        $this->dropIndex('idx_audit_logs_timestamp');
    }

    private function ensureIndex(string $table, string $indexName, array $columns): void
    {
        $db = \Config\Database::connect();

        if ($this->indexExists($db, $indexName)) {
            return;
        }

        $columnList = '"' . implode('", "', $columns) . '"';
        $db->query("CREATE INDEX \"{$indexName}\" ON \"{$table}\" ({$columnList})");
    }

    private function dropIndex(string $indexName): void
    {
        $db = \Config\Database::connect();

        if (!$this->indexExists($db, $indexName)) {
            return;
        }

        $db->query("DROP INDEX \"{$indexName}\"");
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
