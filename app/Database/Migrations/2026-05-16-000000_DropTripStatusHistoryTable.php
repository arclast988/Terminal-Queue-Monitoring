<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Drops the trip_status_history table.
 *
 * Rationale: This table is redundant because:
 * - The queue table's status + arrival_time + departure_time already captures
 *   the full lifecycle of each trip.
 * - The logs table already records every user action with timestamps and user_id
 *   for audit purposes.
 * - Trip history is retrieved by querying queue WHERE status='departed'.
 */
class DropTripStatusHistoryTable extends Migration
{
    public function up()
    {
        // Drop foreign key constraints first, then the table
        $this->forge->dropForeignKey('trip_status_history', 'trip_status_history_queue_id_foreign');
        $this->forge->dropForeignKey('trip_status_history', 'trip_status_history_updated_by_user_id_foreign');
        $this->forge->dropTable('trip_status_history');
    }

    public function down()
    {
        // Recreate the table if we need to roll back
        $this->forge->addField([
            'id'                 => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'queue_id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'status'             => ['type' => 'VARCHAR', 'constraint' => 50],
            'timestamp'          => ['type' => 'DATETIME', 'null' => true],
            'updated_by_user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('queue_id', 'queue', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('updated_by_user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('trip_status_history');
    }
}
