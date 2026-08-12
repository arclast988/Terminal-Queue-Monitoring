<?php

namespace App\Models;

use CodeIgniter\Model;

class QueueModel extends Model
{
    protected $table            = 'queue';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['vehicle_id', 'route_id', 'status', 'current_passengers', 'position', 'arrival_time', 'estimated_departure', 'departure_time'];

    // Dates
    protected $useTimestamps = false; // Manually handling arrival_time and departure_time

    /**
     * Recalculate estimated departure times and positions for all active queue items.
     * Items are grouped per route. For each route:
     *   - Boarding vehicles (if any) stay first. If already boarding, their existing
     *     estimated_departure serves as the base anchor for subsequent vehicles.
     *   - Waiting vehicles are assigned sequential departure slots calculated by adding
     *     the matching departure rule wait_minutes to the previous vehicle's departure slot.
     * Also updates global position ranks so boarding vehicles are first, followed by waiting.
     */
    public function recalculateSchedule(?int $targetRouteId = null): void
    {
        $departureRuleModel = new DepartureRuleModel();

        // 1. Fetch active queue items with route & terminal details
        $builder = $this->select('queue.*, routes.terminal_id, routes.destination')
                        ->join('routes', 'routes.id = queue.route_id')
                        ->whereIn('queue.status', ['waiting', 'boarding']);

        if ($targetRouteId !== null) {
            // When a specific route changes, recalculate ALL routes sharing the
            // same destination+terminal so departure slots never overlap.
            $route = (new RouteModel())->find($targetRouteId);
            if ($route) {
                $sameDestRouteIds = (new RouteModel())
                    ->where('destination', $route['destination'])
                    ->where('terminal_id', $route['terminal_id'])
                    ->findColumn('id') ?: [$targetRouteId];
                $builder->whereIn('queue.route_id', $sameDestRouteIds);
            } else {
                $builder->where('queue.route_id', $targetRouteId);
            }
        }

        $activeItems = $builder->findAll();

        if (!empty($activeItems)) {
            // Group items by destination+terminal (not route_id) so vehicles
            // heading to the same place get sequential, non-overlapping slots
            // even when they have different route_id values.
            $grouped = [];
            foreach ($activeItems as $item) {
                $key = $item['terminal_id'] . '|' . $item['destination'];
                $grouped[$key][] = $item;
            }

            foreach ($grouped as $groupKey => $items) {
                // Sort items for this destination: boarding first, then waiting by arrival/id
                usort($items, static function ($a, $b) {
                    $aBoarding = ($a['status'] === 'boarding');
                    $bBoarding = ($b['status'] === 'boarding');
                    if ($aBoarding !== $bBoarding) {
                        return $aBoarding ? -1 : 1;
                    }
                    $cmp = strcmp((string) ($a['arrival_time'] ?? ''), (string) ($b['arrival_time'] ?? ''));
                    return $cmp !== 0 ? $cmp : ((int) $a['id'] <=> (int) $b['id']);
                });

                $terminalId = (int) ($items[0]['terminal_id'] ?? 1);
                $groupRouteIds = array_values(array_unique(array_column($items, 'route_id')));

                $prevEstDeparture = null;

                foreach ($items as $index => $item) {
                    if ($index === 0) {
                        // First active vehicle on this destination
                        if ($item['status'] === 'boarding' && !empty($item['estimated_departure'])) {
                            $estTime = $item['estimated_departure'];
                        } else {
                            $waitMinutes = $this->resolveGroupInterval($departureRuleModel, date('H:i:s'), $terminalId, $groupRouteIds);
                            $estTime = date('Y-m-d H:i:s', strtotime("+$waitMinutes minutes"));
                        }
                    } else {
                        // Subsequent vehicles: offset from previous vehicle's slot using the departure rule active at that previous departure time
                        $prevTimeStr = date('H:i:s', strtotime($prevEstDeparture));
                        $waitMinutes = $this->resolveGroupInterval($departureRuleModel, $prevTimeStr, $terminalId, $groupRouteIds);
                        $baseTimestamp = strtotime($prevEstDeparture);
                        $estTime = date('Y-m-d H:i:s', strtotime("+$waitMinutes minutes", $baseTimestamp));
                    }

                    $prevEstDeparture = $estTime;

                    if ($item['estimated_departure'] !== $estTime) {
                        $this->update($item['id'], ['estimated_departure' => $estTime]);
                    }
                }
            }
        }

        // 2. Update global position ranks
        $allActive = $this->whereIn('status', ['waiting', 'boarding'])->findAll();
        usort($allActive, static function ($a, $b) {
            $aBoarding = ($a['status'] === 'boarding');
            $bBoarding = ($b['status'] === 'boarding');
            if ($aBoarding !== $bBoarding) {
                return $aBoarding ? -1 : 1;
            }
            $aEst = (string) ($a['estimated_departure'] ?? '');
            $bEst = (string) ($b['estimated_departure'] ?? '');
            if ($aEst !== '' && $bEst !== '') {
                $cmp = strcmp($aEst, $bEst);
                if ($cmp !== 0) {
                    return $cmp;
                }
            }
            $cmp = strcmp((string) ($a['arrival_time'] ?? ''), (string) ($b['arrival_time'] ?? ''));
            return $cmp !== 0 ? $cmp : ((int) $a['id'] <=> (int) $b['id']);
        });

        $pos = 1;
        foreach ($allActive as $row) {
            if ((int) $row['position'] !== $pos) {
                $this->update($row['id'], ['position' => $pos]);
            }
            $pos++;
        }
    }

    /**
     * Renumber the active queue's `position` and update departure schedules.
     */
    public function reorderByDeparture(): void
    {
        $this->recalculateSchedule();
    }

    /**
     * Resolve the departure interval for a destination group.
     * Checks all route_ids in the group and returns the route-specific rule
     * if one exists, otherwise falls back to the terminal default.
     * This ensures all vehicles heading to the same destination use the same
     * interval, even if they have different route_id values.
     */
    private function resolveGroupInterval(DepartureRuleModel $ruleModel, string $time, int $terminalId, array $routeIds): int
    {
        // Try each route_id to find a route-specific rule
        foreach ($routeIds as $routeId) {
            $rule = $ruleModel->getRuleForTime($time, $terminalId, (int) $routeId);
            // If this rule has a route_id, it's a route-specific match (not a fallback)
            if (!empty($rule['route_id'])) {
                return (int) $rule['wait_minutes'];
            }
        }
        // No route-specific rule found — use terminal default
        $rule = $ruleModel->getRuleForTime($time, $terminalId, null);
        return (int) $rule['wait_minutes'];
    }

    /**
     * Scope: join vehicles, routes, and terminals for full queue data.
     * Usage: $queueModel->withFullJoins()->findAll()
     */
    public function withFullJoins(): self
    {
        return $this->join('vehicles', 'vehicles.id = queue.vehicle_id')
                    ->join('routes', 'routes.id = queue.route_id')
                    ->join('terminals', 'terminals.id = routes.terminal_id');
    }
}
