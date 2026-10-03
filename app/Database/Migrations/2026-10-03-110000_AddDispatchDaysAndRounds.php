<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDispatchDaysAndRounds extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('dispatch_rounds')) {
            $this->forge->addField([
                'id' => ['type' => 'INT', 'auto_increment' => true],
                'terminal_id' => ['type' => 'INT'],
                'destination' => ['type' => 'VARCHAR', 'constraint' => 255],
                'service_date' => ['type' => 'DATE'],
                'round_number' => ['type' => 'INT', 'default' => 1],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['terminal_id', 'destination']);
            $this->forge->createTable('dispatch_rounds');
        }
        foreach (['day_of_week', 'round_number'] as $field) {
            if (!$this->db->fieldExists($field, 'departure_rules')) {
                $this->forge->addColumn('departure_rules', [$field => ['type' => 'INT', 'null' => true]]);
            }
        }
        if (!$this->db->fieldExists('round_number', 'queue')) {
            $this->forge->addColumn('queue', ['round_number' => ['type' => 'INT', 'default' => 1]]);
        }
        if (!$this->db->fieldExists('boarding_start', 'queue')) {
            $this->forge->addColumn('queue', ['boarding_start' => ['type' => 'DATETIME', 'null' => true]]);
        }
        // Initialize waiting windows on the five-minute grid; ongoing boarding is preserved.
        (new \App\Models\QueueModel($this->db))->recalculateSchedule(null, true);
    }

    public function down()
    {
        $this->forge->dropTable('dispatch_rounds', true);
        $this->forge->dropColumn('departure_rules', ['day_of_week', 'round_number']);
        $this->forge->dropColumn('queue', ['round_number', 'boarding_start']);
    }
}
