<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds performance indexes to high-traffic columns.
 *
 * The application repeatedly executes queries like:
 *   - SELECT ... WHERE status IN ('waiting','boarding') ORDER BY position
 *   - SELECT ... WHERE status = 'departed' ORDER BY departure_time DESC
 *   - SELECT ... FROM logs ORDER BY timestamp DESC
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
        $this->ensureIndex('queue', 'idx_queue_status', '(`status`)');
        $this->ensureIndex('queue', 'idx_queue_status_position', '(`status`, `position`)');
        $this->ensureIndex('queue', 'idx_queue_departure_time', '(`departure_time`)');
        $this->ensureIndex('logs',  'idx_logs_timestamp', '(`timestamp`)');
    }

    public function down()
    {
        $this->dropIndex('queue', 'idx_queue_status');
        $this->dropIndex('queue', 'idx_queue_status_position');
        $this->dropIndex('queue', 'idx_queue_departure_time');
        $this->dropIndex('logs',  'idx_logs_timestamp');
    }

    private function ensureIndex(string $table, string $indexName, string $columnsSql): void
    {
        $db = \Config\Database::connect();

        $row = $db->query(
            'SELECT COUNT(1) AS c
             FROM information_schema.statistics
             WHERE table_schema = DATABASE()
               AND table_name   = ?
               AND index_name   = ?',
            [$table, $indexName]
        )->getRow();

        if ((int) ($row->c ?? 0) === 0) {
            $db->query("CREATE INDEX `{$indexName}` ON `{$table}` {$columnsSql}");
        }
    }

    private function dropIndex(string $table, string $indexName): void
    {
        $db = \Config\Database::connect();

        $row = $db->query(
            'SELECT COUNT(1) AS c
             FROM information_schema.statistics
             WHERE table_schema = DATABASE()
               AND table_name   = ?
               AND index_name   = ?',
            [$table, $indexName]
        )->getRow();

        if ((int) ($row->c ?? 0) > 0) {
            $db->query("DROP INDEX `{$indexName}` ON `{$table}`");
        }
    }
}
