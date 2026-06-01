<?php

namespace App\Models;

use CodeIgniter\Model;

class DepartureRuleModel extends Model
{
    protected $table = 'departure_rules';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['terminal_id', 'route_id', 'time_from', 'time_to', 'wait_minutes', 'label'];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get the matching departure rule for a given time.
     *
     * Resolution order:
     *   1. A route-specific rule (route_id = $routeId) covering $time — the per-destination interval.
     *   2. A terminal-wide default rule (route_id IS NULL) for $terminalId covering $time.
     *   3. A hard-coded default (30 minutes).
     *
     * Returns the full rule array, or the default fallback if nothing matches.
     */
    public function getRuleForTime(string $time, int $terminalId, ?int $routeId = null): array
    {
        // 1. Route-specific rule (destination override).
        if ($routeId !== null) {
            $rule = $this->where('route_id', $routeId)
                ->where('time_from <=', $time)
                ->where('time_to >', $time)
                ->first();

            if ($rule) {
                return $rule;
            }
        }

        // 2. Terminal-wide default rule (no destination attached).
        $rule = $this->where('terminal_id', $terminalId)
            ->where('route_id', null)
            ->where('time_from <=', $time)
            ->where('time_to >', $time)
            ->first();

        if ($rule) {
            return $rule;
        }

        // 3. Fallback: no matching rule.
        return [
            'wait_minutes' => 30,
            'label' => 'Default (no rule matched)',
            'time_from' => null,
            'time_to' => null,
        ];
    }

    /**
     * Get the wait minutes for a given time (optionally route-specific).
     */
    public function getWaitMinutesForTime(string $time, int $terminalId, ?int $routeId = null): int
    {
        $rule = $this->getRuleForTime($time, $terminalId, $routeId);
        return (int) $rule['wait_minutes'];
    }
}
