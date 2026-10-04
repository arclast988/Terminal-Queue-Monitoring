<?php

namespace App\Database\Migrations;

use App\Models\DepartureRuleModel;
use App\Models\QueueModel;
use CodeIgniter\Database\Migration;

/**
 * Close gaps left by deleted or edited rules so each destination's rounds
 * read 1, 2, 3... (e.g. existing rounds 1, 2, 4 become 1, 2, 3).
 */
class CompactDepartureRuleRounds extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('departure_rules') || !$this->db->tableExists('routes')
            || !$this->db->fieldExists('round_number', 'departure_rules')) {
            return;
        }

        $model = new DepartureRuleModel($this->db);
        $scopes = $this->db->table('departure_rules')->select('terminal_id, route_id')
            ->distinct()->get()->getResultArray();
        foreach ($scopes as $scope) {
            $model->compactRounds((int) $scope['terminal_id'], $scope['route_id'] !== null ? (int) $scope['route_id'] : null);
        }

        if ($this->db->tableExists('queue')) {
            (new QueueModel($this->db))->recalculateSchedule();
        }
    }

    public function down()
    {
        // Renumbered rounds are operational data; the old gaps are not restored.
    }
}
