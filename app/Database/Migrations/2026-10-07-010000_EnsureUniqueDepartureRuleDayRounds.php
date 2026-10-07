<?php

namespace App\Database\Migrations;

use App\Models\DepartureRuleModel;
use CodeIgniter\Database\Migration;

class EnsureUniqueDepartureRuleDayRounds extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('departure_rules') || !$this->db->tableExists('routes')
            || !$this->db->fieldExists('round_number', 'departure_rules')) {
            return;
        }
        $model = new DepartureRuleModel($this->db);
        $scopes = $this->db->table('departure_rules')->select('terminal_id, route_id')
            ->distinct()->orderBy('terminal_id', 'ASC')->orderBy('route_id', 'ASC')
            ->get()->getResultArray();
        foreach ($scopes as $scope) {
            $model->repairDuplicateDayRounds((int) $scope['terminal_id'],
                $scope['route_id'] !== null ? (int) $scope['route_id'] : null);
        }
    }

    public function down()
    {
        // Keep corrected operational round numbers when rolling back the code.
    }
}
