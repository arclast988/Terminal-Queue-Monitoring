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
    protected $allowedFields    = ['vehicle_id', 'driver_name', 'operator_name', 'plate_number', 'route_id', 'status', 'current_passengers', 'position', 'arrival_time', 'estimated_departure', 'departure_time'];

    // Dates
    protected $useTimestamps = false; // Manually handling arrival_time and departure_time

    protected $afterFind    = ['uppercaseFieldsOnRead'];
    protected $beforeInsert = ['uppercaseFieldsOnWrite'];
    protected $beforeUpdate = ['uppercaseFieldsOnWrite'];

    /**
     * Automatically ensure plate_number and operator_name are uppercase on write.
     */
    protected function uppercaseFieldsOnWrite(array $data): array
    {
        if (isset($data['data']['plate_number'])) {
            $data['data']['plate_number'] = strtoupper(trim((string)$data['data']['plate_number']));
        }
        if (isset($data['data']['operator_name'])) {
            $data['data']['operator_name'] = strtoupper(trim((string)$data['data']['operator_name']));
        }
        return $data;
    }

    /**
     * Ensure plate_number and operator_name are uppercase on read.
     */
    protected function uppercaseFieldsOnRead(array $data): array
    {
        if (empty($data['data'])) {
            return $data;
        }

        if (!empty($data['singleton'])) {
            if (isset($data['data']['plate_number'])) {
                $data['data']['plate_number'] = strtoupper((string)$data['data']['plate_number']);
            }
            if (isset($data['data']['operator_name'])) {
                $data['data']['operator_name'] = strtoupper((string)$data['data']['operator_name']);
            }
            return $data;
        }

        foreach ($data['data'] as &$row) {
            if (is_array($row)) {
                if (isset($row['plate_number'])) {
                    $row['plate_number'] = strtoupper((string)$row['plate_number']);
                }
                if (isset($row['operator_name'])) {
                    $row['operator_name'] = strtoupper((string)$row['operator_name']);
                }
            }
        }

        return $data;
    }

    /**
     * Recalculate estimated departure times and positions for all active queue items.
     * Items are grouped per terminal and destination. For each queue line:
     *   - Boarding vehicles (if any) stay first. Preserve their departure slot.
     *   - Preserve the earliest waiting slot across routine queue mutations so
     *     adding/reordering vehicles cannot restart the line's countdown.
     *   - Waiting vehicles are assigned sequential departure slots calculated by adding
     *     the matching departure rule wait_minutes to the previous vehicle's departure slot.
     * Position is the canonical dispatcher-managed order. Boarding vehicles remain
     * first, followed by waiting vehicles in their saved position order.
     */
    public function recalculateSchedule(?int $targetRouteId = null, bool $resetWaitingAnchor = false): void
    {
        $db = $this->db;
        $db->transStart();

        // 1. Acquire transaction-level PostgreSQL advisory lock to serialize concurrent recalculations
        $driver = strtolower($db->DBDriver ?? '');
        if (str_contains($driver, 'postgre')) {
            // Targeted and full recalculations can touch the same destination.
            $lockKey = crc32('queue_recalc_0');
            if ($lockKey > 2147483647) {
                $lockKey -= 4294967296;
            }
            $db->query('SELECT pg_advisory_xact_lock(?)', [(int) $lockKey]);
        }

        $departureRuleModel = new DepartureRuleModel($db);
        $now = time();

        // 2. Fetch active queue items with route & terminal details
        $builder = $this->select('queue.*, routes.terminal_id, routes.destination')
                        ->join('routes', 'routes.id = queue.route_id')
                        ->whereIn('queue.status', ['waiting', 'boarding']);

        if ($targetRouteId !== null) {
            // When a specific route changes, recalculate ALL routes sharing the
            // same destination+terminal so departure slots never overlap.
            $route = (new RouteModel($db))->find($targetRouteId);
            if ($route) {
                $sameDestRouteIds = (new RouteModel($db))
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
            // Pre-load all departure rules into memory to eliminate N+1 queries in the loop
            $terminalIds = array_values(array_unique(array_filter(array_column($activeItems, 'terminal_id'))));
            $allRules = !empty($terminalIds)
                ? $departureRuleModel->whereIn('terminal_id', $terminalIds)->findAll()
                : $departureRuleModel->findAll();

            // Include sibling routes even if their vehicle type has no active
            // vehicle. The admin stores a destination rule on just one of them.
            $routesByDestination = [];
            $allRoutes = (new RouteModel($db))->whereIn('terminal_id', $terminalIds)->findAll();
            foreach ($allRoutes as $route) {
                $key = $route['terminal_id'] . '|' . $route['destination'];
                $routesByDestination[$key][] = (int) $route['id'];
            }

            // Group items by destination+terminal (not route_id) so vehicles
            // heading to the same place get sequential, non-overlapping slots
            // even when they have different route_id values.
            $grouped = [];
            foreach ($activeItems as $item) {
                $key = $item['terminal_id'] . '|' . $item['destination'];
                $grouped[$key][] = $item;
            }

            $batchUpdates = [];

            foreach ($grouped as $groupKey => $items) {
                // Sort items for this destination: boarding first, then by the
                // dispatcher-managed queue position. Arrival/id are deterministic
                // fallbacks for legacy rows with no valid position or duplicate values.
                usort($items, static function ($a, $b) {
                    $aBoarding = ($a['status'] === 'boarding');
                    $bBoarding = ($b['status'] === 'boarding');
                    if ($aBoarding !== $bBoarding) {
                        return $aBoarding ? -1 : 1;
                    }

                    $aPosition = (int) ($a['position'] ?? 0);
                    $bPosition = (int) ($b['position'] ?? 0);
                    $aPosition = $aPosition > 0 ? $aPosition : PHP_INT_MAX;
                    $bPosition = $bPosition > 0 ? $bPosition : PHP_INT_MAX;
                    $positionCmp = $aPosition <=> $bPosition;
                    if ($positionCmp !== 0) {
                        return $positionCmp;
                    }

                    $cmp = strcmp((string) ($a['arrival_time'] ?? ''), (string) ($b['arrival_time'] ?? ''));
                    return $cmp !== 0 ? $cmp : ((int) $a['id'] <=> (int) $b['id']);
                });

                $terminalId = (int) ($items[0]['terminal_id'] ?? 1);
                $groupRouteIds = $routesByDestination[$groupKey]
                    ?? array_values(array_unique(array_column($items, 'route_id')));

                $waitingAnchor = null;
                if (!$resetWaitingAnchor) {
                    foreach ($items as $item) {
                        $timestamp = !empty($item['estimated_departure'])
                            ? strtotime($item['estimated_departure']) : false;
                        if ($timestamp !== false && ($waitingAnchor === null || $timestamp < $waitingAnchor)) {
                            $waitingAnchor = $timestamp;
                        }
                    }
                }

                $prevEstDeparture = null;

                foreach ($items as $index => $item) {
                    if ($index === 0) {
                        // First active vehicle on this destination
                        if ($item['status'] === 'boarding' && !empty($item['estimated_departure'])) {
                            $estTime = $item['estimated_departure'];
                        } elseif ($waitingAnchor !== null) {
                            $estTime = date('Y-m-d H:i:s', $waitingAnchor);
                        } else {
                            $waitMinutes = $this->resolveGroupIntervalFromRules($allRules, date('H:i:s', $now), $terminalId, $groupRouteIds);
                            $estTime = date('Y-m-d H:i:s', $now + ($waitMinutes * 60));
                        }
                    } else {
                        // Subsequent vehicles: offset from previous vehicle's slot using the departure rule active at that previous departure time
                        $prevTimeStr = date('H:i:s', strtotime($prevEstDeparture));
                        $waitMinutes = $this->resolveGroupIntervalFromRules($allRules, $prevTimeStr, $terminalId, $groupRouteIds);
                        $baseTimestamp = strtotime($prevEstDeparture);
                        $estTime = date('Y-m-d H:i:s', strtotime("+$waitMinutes minutes", $baseTimestamp));
                    }

                    $prevEstDeparture = $estTime;

                    $updateData = [];
                    if (($item['estimated_departure'] ?? '') !== $estTime) {
                        $updateData['estimated_departure'] = $estTime;
                    }

                    // Assign sequential position (1, 2, 3, ...) for this destination.
                    $routePosition = $index + 1;
                    if ((int) ($item['position'] ?? 0) !== $routePosition) {
                        $updateData['position'] = $routePosition;
                    }

                    if (!empty($updateData)) {
                        $updateData['id'] = $item['id'];
                        $batchUpdates[] = $updateData;
                    }
                }
            }

            if (!empty($batchUpdates)) {
                foreach ($batchUpdates as $upd) {
                    $uid = $upd['id'];
                    unset($upd['id']);
                    $this->update($uid, $upd);
                }
            }
        }

        $db->transComplete();
    }

    /**
     * Renumber the active queue's `position` and update departure schedules.
     */
    public function reorderByDeparture(): void
    {
        $this->recalculateSchedule();
    }

    /**
     * Resolve departure interval using in-memory pre-loaded rules (eliminates N+1 DB queries).
     */
    public function resolveGroupIntervalFromRules(array $allRules, string $time, int $terminalId, array $routeIds): int
    {
        return (int) DepartureRuleModel::resolveRuleFromRules($allRules, $time, $terminalId, $routeIds)['wait_minutes'];
    }

    /**
     * Legacy adapter for resolving departure interval.
     */
    public function resolveGroupInterval(DepartureRuleModel $ruleModel, string $time, int $terminalId, array $routeIds): int
    {
        $rules = $ruleModel->where('terminal_id', $terminalId)->findAll();
        return $this->resolveGroupIntervalFromRules($rules, $time, $terminalId, $routeIds);
    }

    /**
     * Scope: join vehicles, routes, and terminals for full queue data.
     * Usage: $queueModel->withFullJoins()->findAll()
     */
    public function withFullJoins(): self
    {
        // LEFT joins preserve queue/history rows when a vehicle, route or
        // terminal was deleted (FKs use SET NULL / CASCADE). INNER joins
        // would silently drop departed history.
        return $this->join('vehicles', 'vehicles.id = queue.vehicle_id', 'left')
                    ->join('routes', 'routes.id = queue.route_id', 'left')
                    ->join('terminals', 'terminals.id = routes.terminal_id', 'left');
    }

    /**
     * Automatically purge departed queue records older than the specified retention days.
     *
     * @param int|null $days Number of days to retain departure records (defaults to system setting departure_retention_days())
     * @return int Number of deleted rows
     */
    public function purgeOldDepartures(?int $days = null): int
    {
        $days = ($days !== null && $days >= 1) ? $days : departure_retention_days();
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        return (int) $this->where('status', 'departed')
            ->where('departure_time <', $cutoff)
            ->delete();
    }
}
