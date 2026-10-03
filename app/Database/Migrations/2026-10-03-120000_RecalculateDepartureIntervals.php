<?php

namespace App\Database\Migrations;

use App\Models\QueueModel;
use CodeIgniter\Database\Migration;

/**
 * Repair existing active departure slots when the corrected scheduler deploys.
 * Preserve the line's first slot and leave departed/canceled history untouched.
 */
class RecalculateDepartureIntervals extends Migration
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
        // Corrected timestamps are operational data; do not restore bad slots.
    }
}
