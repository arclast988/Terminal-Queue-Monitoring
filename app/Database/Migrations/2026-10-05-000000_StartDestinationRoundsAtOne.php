<?php

namespace App\Database\Migrations;

use App\Models\DepartureRuleModel;
use CodeIgniter\Database\Migration;

/** Repair legacy destination scopes whose first (or only) round was above 1. */
class StartDestinationRoundsAtOne extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('departure_rules') || !$this->db->tableExists('routes')
            || !$this->db->fieldExists('round_number', 'departure_rules')) {
            return;
        }

        $model = new DepartureRuleModel($this->db);
        $scopes = $this->db->table('departure_rules')->select('terminal_id, route_id')
            ->where('route_id IS NOT NULL')->distinct()->get()->getResultArray();
        foreach ($scopes as $scope) {
            $model->compactRounds((int) $scope['terminal_id'], (int) $scope['route_id']);
        }
        // Only the numbers change. Boarding starts, departure times, intervals
        // and completed trips retain their existing values.
    }

    public function down()
    {
        // Operational numbering is not reverted to the previous gaps.
    }
}
