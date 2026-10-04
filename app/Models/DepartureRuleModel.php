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
    protected $allowedFields = ['terminal_id', 'route_id', 'time_from', 'time_to', 'wait_minutes', 'label', 'day_of_week', 'days_of_week', 'round_number'];

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
     * Every rule, including an explicit round, must cover the selected day
     * and time. An expired round cannot override a currently active rule.
     *
     * Returns the full rule array, or the default fallback if nothing matches.
     */
    public function getRuleForTime(string $time, int $terminalId, ?int $routeId = null, int $roundNumber = 1): array
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
            $routeIds,
            $roundNumber
        );
    }

    /**
     * Shared resolution for queue slots, boarding and registration. If legacy
     * rules overlap, the most recently edited rule wins consistently, regardless
     * of which vehicle type happens to be active in the queue.
     */
    public static function resolveRuleFromRules(array $rules, string $time, int $terminalId, array $routeIds, int $roundNumber = 1): array
    {
        $timestamp = strtotime($time);
        $timeStr = date('H:i:s', $timestamp);
        $day = (int) date('N', $timestamp);
        $routeIds = array_map('intval', $routeIds);
        $destinationRule = null;
        $terminalRule = null;
        $priority = static fn(array $r): array => [
            (int) (count(departure_rule_days($r)) < 7) + (int) !empty($r['round_number']),
            (int) (count(departure_rule_days($r)) < 7),
            ($r['updated_at'] ?? ''), (int) ($r['id'] ?? 0),
        ];
        $withinWindow = static fn(array $r): bool => $r['time_from'] <= $timeStr
            && ($timeStr >= '23:59:00' ? $r['time_to'] >= '23:59:00' : $r['time_to'] > $timeStr);

        foreach ($rules as $rule) {
            if ((int) ($rule['terminal_id'] ?? 0) !== $terminalId
                || !in_array($day, departure_rule_days($rule), true)
                || (!empty($rule['round_number']) && (int) $rule['round_number'] !== $roundNumber)
                || empty($rule['time_from']) || empty($rule['time_to'])) {
                continue;
            }

            $isDestinationRule = !empty($rule['route_id'])
                && in_array((int) $rule['route_id'], $routeIds, true);
            if (!$isDestinationRule && !empty($rule['route_id'])) {
                continue;
            }

            if (!$withinWindow($rule)) continue;
            $matched = $isDestinationRule ? $destinationRule : $terminalRule;
            if ($matched === null || $priority($rule) > $priority($matched)) {
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
     * Determine the rules actually used by the selected round in each route group.
     */
    public static function activeRuleDestinations(array $rules, string $time, array $routes, array $roundStates): array
    {
        $date = date('Y-m-d', strtotime($time));
        $rounds = [];
        foreach ($roundStates as $state) {
            if ($state['service_date'] === $date) $rounds[$state['terminal_id'] . '|' . $state['destination']] = (int) $state['round_number'];
        }
        $groups = [];
        foreach ($routes as $route) {
            $key = $route['terminal_id'] . '|' . $route['destination'];
            $groups[$key]['terminal_id'] = (int) $route['terminal_id'];
            $groups[$key]['destination'] = $route['destination'];
            $groups[$key]['ids'][] = (int) $route['id'];
            $groups[$key]['has_active'] = ($groups[$key]['has_active'] ?? false) || ($route['status'] ?? 'active') === 'active';
        }
        $active = [];
        foreach ($groups as $key => $group) {
            if (!$group['has_active']) continue;
            $matched = self::resolveRuleFromRules($rules, $time, $group['terminal_id'], $group['ids'], $rounds[$key] ?? 1);
            if (!empty($matched['id'])) $active[(int) $matched['id']][] = strtoupper($group['destination']);
        }
        foreach ($active as &$destinations) {
            $destinations = array_values(array_unique($destinations));
            sort($destinations);
        }
        unset($destinations);
        return $active;
    }

    /** Get the wait minutes for a given time (optionally route-specific). */
    public function getWaitMinutesForTime(string $time, int $terminalId, ?int $routeId = null, int $roundNumber = 1): int
    {
        $rule = $this->getRuleForTime($time, $terminalId, $routeId, $roundNumber);
        return (int) $rule['wait_minutes'];
    }

    /**
     * Renumber a destination's rounds to 1, 2, 3... across its vehicle types.
     * Terminal-wide defaults close gaps while retaining their starting number.
     * The selected dispatch round and active trips are remapped with destination
     * rules; completed trips and schedule timestamps retain their values.
     */
    public function compactRounds(int $terminalId, ?int $routeId): void
    {
        $routeIds = [];
        $destination = null;
        if ($routeId !== null) {
            $routeModel = new RouteModel($this->db);
            $route = $routeModel->find($routeId);
            if (!$route) return;
            $terminalId = (int) $route['terminal_id'];
            $destination = $route['destination'];
            $routeIds = array_map('intval', $routeModel->getDestinationRouteIds($terminalId, $destination));
            if (!$routeIds) return;
        }

        $builder = $this->db->table($this->table)->select('id, round_number')->where('terminal_id', $terminalId);
        $routeIds ? $builder->whereIn('route_id', $routeIds) : $builder->where('route_id', null);
        $rules = $builder->get()->getResultArray();

        $rounds = array_values(array_unique(array_map(static fn(array $r): int => max(1, (int) $r['round_number']), $rules)));
        sort($rounds);
        if (!$rounds) return;

        $map = [];
        // A destination's first rule is Round 1, including a lone legacy
        // Round 2. Terminal defaults keep their starting number because
        // destination rules can reference those shared round numbers.
        $firstRound = $destination !== null ? 1 : $rounds[0];
        foreach ($rounds as $index => $round) {
            $expected = $firstRound + $index;
            if ($round !== $expected) $map[$round] = $expected;
        }
        if (!$map) return;

        $this->db->transStart();
        foreach ($rules as $rule) {
            $old = max(1, (int) $rule['round_number']);
            if (isset($map[$old])) {
                $this->db->table($this->table)->where('id', $rule['id'])->update(['round_number' => $map[$old]]);
            }
        }

        if ($destination === null) {
            $this->db->transComplete();
            return;
        }
        // Ascending order is safe: every mapped value is lower than its source.
        ksort($map);
        foreach ($map as $old => $new) {
            if ($this->db->tableExists('dispatch_rounds')) {
                $this->db->table('dispatch_rounds')->where('terminal_id', $terminalId)
                    ->where('destination', $destination)->where('round_number', $old)
                    ->update(['round_number' => $new]);
            }
            if ($this->db->tableExists('queue') && $this->db->fieldExists('round_number', 'queue')) {
                $this->db->table('queue')->whereIn('route_id', $routeIds)
                    ->whereIn('status', ['waiting', 'boarding'])->where('round_number', $old)
                    ->update(['round_number' => $new]);
            }
        }
        $this->db->transComplete();
    }
}
