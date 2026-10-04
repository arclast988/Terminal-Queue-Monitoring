<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDepartureRuleWeekdays extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('days_of_week', 'departure_rules')) {
            $this->forge->addColumn('departure_rules', [
                'days_of_week' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            ]);
            $this->db->resetDataCache();
        }
        // Existing single-day rules remain valid. Unassigned rounds become Round 1.
        $this->db->table('departure_rules')->where('round_number', null)->update(['round_number' => 1]);
        (new \App\Models\QueueModel($this->db))->recalculateSchedule(null, true);
    }

    public function down()
    {
        $this->forge->dropColumn('departure_rules', 'days_of_week');
    }
}
