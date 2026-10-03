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
     *   1. A rule for any route sharing the requested destination and terminal.
     *   2. A terminal-wide default rule (route_id IS NULL) for $terminalId covering $time.
     *   3. A hard-coded default (30 minutes).
     *
     * Returns the full rule array, or the default fallback if nothing matches.
     */
    public function getRuleForTime(string $time, int $terminalId, ?int $routeId = null): array
    {
        $routeIds = [];
        if ($routeId !== null) {
            $routeModel = new RouteModel($this->db);
            $route = $routeModel->where('terminal_id', $terminalId)->find($routeId);
            if ($route) {
                $routeIds = $routeModel->getDestinationRouteIds($terminalId, $route['destination']);
            }
        }

        return self::resolveRuleFromRules(
            $this->where('terminal_id', $terminalId)->findAll(),
            $time,
            $terminalId,
            $routeIds
        );
    }

    /**
     * Shared resolution for queue slots, boarding and registration. If legacy
     * rules overlap, the most recently edited rule wins consistently, regardless
     * of which vehicle type happens to be active in the queue.
     */
    public static function resolveRuleFromRules(array $rules, string $time, int $terminalId, array $routeIds): array
    {
        $timeStr = date('H:i:s', strtotime($time));
        $routeIds = array_map('intval', $routeIds);
        $destinationRule = null;
        $terminalRule = null;

        foreach ($rules as $rule) {
            if ((int) ($rule['terminal_id'] ?? 0) !== $terminalId
                || empty($rule['time_from']) || empty($rule['time_to'])
                || $rule['time_from'] > $timeStr
                || ($timeStr >= '23:59:00'
                    ? $rule['time_to'] < '23:59:00'
                    : $rule['time_to'] <= $timeStr)) {
                continue;
            }

            $isDestinationRule = !empty($rule['route_id'])
                && in_array((int) $rule['route_id'], $routeIds, true);
            if (!$isDestinationRule && !empty($rule['route_id'])) {
                continue;
            }

            $matched = $isDestinationRule ? $destinationRule : $terminalRule;
            if ($matched === null || [($rule['updated_at'] ?? ''), (int) ($rule['id'] ?? 0)]
                > [($matched['updated_at'] ?? ''), (int) ($matched['id'] ?? 0)]) {
                if ($isDestinationRule) {
                    $destinationRule = $rule;
                } else {
                    $terminalRule = $rule;
                }
            }
        }

        return $destinationRule ?? $terminalRule ?? [
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
