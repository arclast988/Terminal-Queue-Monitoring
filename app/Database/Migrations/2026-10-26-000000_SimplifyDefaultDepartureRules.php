<?php

namespace App\Database\Migrations;

use App\Libraries\DepartureRuleDefaults;
use CodeIgniter\Database\Migration;

/** Replace untouched legacy seed sets; preserve schedules created or edited by users. */
class SimplifyDefaultDepartureRules extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('departure_rules') || !$this->db->tableExists('terminals')) {
            return;
        }
        $legacy = [
            ['00:00:00', '05:00:00', 60, 'Late Night / Early Morning'],
            ['05:00:00', '09:00:00', 30, 'Morning Rush'],
            ['09:00:00', '12:00:00', 40, 'Mid-Morning'],
            ['12:00:00', '15:00:00', 40, 'Afternoon'],
            ['15:00:00', '18:00:00', 30, 'Afternoon Rush'],
            ['18:00:00', '21:00:00', 40, 'Evening'],
            ['21:00:00', '23:59:59', 60, 'Late Night'],
        ];
        $this->db->transStart();
        $rules = $this->db->table('departure_rules')->get()->getResultArray();
        $changed = false;
        if (!$rules) {
            $terminal = $this->db->table('terminals')->orderBy('id')->get(1)->getRowArray();
            if ($terminal) {
                $this->insertDefaults((int) $terminal['id']);
                $changed = true;
            }
        } else {
            $groups = [];
            foreach ($rules as $rule) {
                if (!empty($rule['route_id']) || !empty($rule['created_at']) || !empty($rule['updated_at'])
                    || count(departure_rule_days($rule)) !== 7) continue;
                $signature = [$rule['time_from'], $rule['time_to'], (int) $rule['wait_minutes'], $rule['label']];
                foreach (DepartureRuleDefaults::rules() as $default) {
                    if ($signature === [$default['time_from'], $default['time_to'], $default['wait_minutes'], $default['label']]
                        && !empty($rule['round_number'])) {
                        // Time-based terminal defaults apply to every dispatch round.
                        $this->db->table('departure_rules')->where('id', $rule['id'])->update(['round_number' => null]);
                        $changed = true;
                    }
                }
                $index = array_search($signature, $legacy, true);
                if ($index !== false) $groups[(int) $rule['terminal_id']][$index][] = (int) $rule['id'];
            }
            foreach ($groups as $terminalId => $matches) {
                // A partial or changed set is left for the owner to manage through Select.
                if (count($matches) !== 7 || array_filter($matches, static fn ($ids) => count($ids) !== 1)) continue;
                $ids = array_merge(...array_values($matches));
                $this->db->table('departure_rules')->whereIn('id', $ids)->delete();
                $this->insertDefaults($terminalId);
                $changed = true;
            }
        }
        $this->db->transComplete();
        if (!$this->db->transStatus()) throw new \RuntimeException('Could not update default departure rules.');
        if ($changed && $this->db->tableExists('queue')) {
            (new \App\Models\QueueModel($this->db))->recalculateSchedule(null, true);
        }
    }

    private function insertDefaults(int $terminalId): void
    {
        foreach (DepartureRuleDefaults::rules() as $rule) {
            $this->db->table('departure_rules')->insert($rule + [
                'terminal_id' => $terminalId, 'route_id' => null,
                'day_of_week' => null, 'days_of_week' => null, 'round_number' => null,
            ]);
        }
    }

    public function down()
    {
        // Do not recreate removed demo schedules or overwrite subsequent edits.
    }
}
