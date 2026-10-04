<?php

namespace App\Database\Migrations;

use App\Models\QueueModel;
use CodeIgniter\Database\Migration;

/** Round existing active departures up without restarting boarding or changing history. */
class AlignActiveDepartureSlots extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('queue') && $this->db->tableExists('routes')
            && $this->db->tableExists('departure_rules')) {
            (new QueueModel($this->db))->recalculateSchedule();
        }
    }

    public function down()
    {
        // Keep corrected operational timestamps when rolling back code.
    }
}
