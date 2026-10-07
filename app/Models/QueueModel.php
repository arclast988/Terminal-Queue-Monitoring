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
    protected $allowedFields    = ['vehicle_id', 'driver_name', 'operator_name', 'plate_number', 'route_id', 'status', 'current_passengers', 'position', 'arrival_time', 'estimated_departure', 'departure_time', 'round_number', 'boarding_start'];

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
     *   - Boarding vehicles (if any) stay first. Preserve their departure slot,
     *     rounded up to a five-minute boundary when repairing legacy times.
     *   - Preserve the earliest waiting slot across routine queue mutations so
     *     adding/reordering vehicles cannot restart the line's countdown.
     *   - Waiting vehicles are assigned sequential departure slots calculated by adding
     *     the matching departure rule wait_minutes to the previous vehicle's departure slot.
     * Position is the canonical dispatcher-managed order. Boarding vehicles remain
     * first, followed by waiting vehicles in their saved position order.
     */
    public function recalculateSchedule(?int $targetRouteId = null, bool $resetWaitingAnchor = false, ?int $startAfter = null, ?int $now = null): void
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
        $now ??= time();

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
                $anchorStart = null;
                if (!$resetWaitingAnchor && $startAfter === null) {
                    foreach ($items as $candidate) {
                        if (!empty($candidate['boarding_start'])) {
                            $candidateStart = strtotime($candidate['boarding_start']);
                            $anchorStart = $anchorStart === null ? $candidateStart : min($anchorStart, $candidateStart);
                        }
                    }
                }

                foreach ($items as $index => $item) {
                    if ($index === 0) {
                        // First active vehicle on this destination
                        if ($item['status'] === 'boarding' && !empty($item['estimated_departure'])) {
                            $estTime = $item['estimated_departure'];
                            $legacyInterval = $this->resolveGroupIntervalFromRules($allRules, $estTime, $terminalId, $groupRouteIds, (int) ($item['round_number'] ?? 1));
                            $boardingStart = !empty($item['boarding_start']) ? strtotime($item['boarding_start']) : strtotime($estTime) - $legacyInterval * 60;
                        } elseif ($waitingAnchor !== null && !$resetWaitingAnchor && $startAfter === null) {
                            $estTime = date('Y-m-d H:i:s', $waitingAnchor);
                            $legacyInterval = $this->resolveGroupIntervalFromRules($allRules, $estTime, $terminalId, $groupRouteIds, (int) ($item['round_number'] ?? 1));
                            $boardingStart = $anchorStart ?? $waitingAnchor - $legacyInterval * 60;
                            if ($anchorStart !== null) {
                                $interval = $this->resolveGroupIntervalFromRules($allRules, date('Y-m-d H:i:s', $boardingStart), $terminalId, $groupRouteIds, (int) ($item['round_number'] ?? 1));
                                $estTime = date('Y-m-d H:i:s', $boardingStart + $interval * 60);
                            }
                        } else {
                            $boardingStart = self::nextFiveMinuteBoundary($startAfter ?? $now);
                            $waitMinutes = $this->resolveGroupIntervalFromRules($allRules, date('Y-m-d H:i:s', $boardingStart), $terminalId, $groupRouteIds, (int) ($item['round_number'] ?? 1));
                            $estTime = date('Y-m-d H:i:s', $boardingStart + ($waitMinutes * 60));
                        }
                    } else {
                        // Subsequent vehicles: offset from previous vehicle's slot using the departure rule active at that previous departure time
                        $boardingStart = self::nextFiveMinuteBoundary(strtotime($prevEstDeparture));
                        $waitMinutes = $this->resolveGroupIntervalFromRules($allRules, date('Y-m-d H:i:s', $boardingStart), $terminalId, $groupRouteIds, (int) ($item['round_number'] ?? 1));
                        $estTime = date('Y-m-d H:i:s', $boardingStart + $waitMinutes * 60);
                    }

                    // Manual boarding can start at any minute, but departure
                    // slots always end on :00, :05, :10, etc. Round only once
                    // so routine refreshes cannot extend the countdown again.
                    $estTime = date('Y-m-d H:i:s', self::nextFiveMinuteBoundary(strtotime($estTime)));
                    $prevEstDeparture = $estTime;

                    $updateData = [];
                    if (($item['boarding_start'] ?? '') !== date('Y-m-d H:i:s', $boardingStart)) {
                        $updateData['boarding_start'] = date('Y-m-d H:i:s', $boardingStart);
                    }
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
    public function resolveGroupIntervalFromRules(array $allRules, string $time, int $terminalId, array $routeIds, int $roundNumber = 1): int
    {
        return (int) DepartureRuleModel::resolveRuleFromRules($allRules, $time, $terminalId, $routeIds, $roundNumber)['wait_minutes'];
    }

    public static function nextFiveMinuteBoundary(int $timestamp): int
    {
        return (int) (ceil($timestamp / 300) * 300);
    }

    /** Keep the full boarding interval, then wait until the next departure slot. */
    public static function departureAfterInterval(int $boardingStart, int $waitMinutes): int
    {
        return self::nextFiveMinuteBoundary($boardingStart + $waitMinutes * 60);
    }

    /** Close yesterday's unfinished trips; history and today's trips stay intact. */
    public function resetForNewDay(?int $now = null): array
    {
        $now ??= time();
        $today = date('Y-m-d', $now);
        $this->db->transStart();
        if (str_contains(strtolower($this->db->DBDriver), 'postgre')) {
            $key = crc32('queue_recalc_0');
            $this->db->query('SELECT pg_advisory_xact_lock(?)', [$key > 2147483647 ? $key - 4294967296 : $key]);
        }
        $ids = array_map('intval', $this->whereIn('status', ['waiting', 'boarding'])
            ->where('arrival_time <', $today . ' 00:00:00')->findColumn('id') ?: []);
        if ($ids) {
            $this->db->table('queue')->whereIn('id', $ids)->update([
                'status' => 'canceled', 'position' => 0,
                'estimated_departure' => null, 'boarding_start' => null,
            ]);
        }
        $rounds = 0;
        if ($this->db->tableExists('dispatch_rounds')) {
            $this->db->table('dispatch_rounds')->where('service_date <', $today)
                ->update(['service_date' => $today, 'round_number' => 1]);
            $rounds = $this->db->affectedRows();
        }
        $this->db->transComplete();
        return $this->db->transStatus() ? ['canceled_ids' => $ids, 'rounds_reset' => $rounds]
            : ['canceled_ids' => [], 'rounds_reset' => 0];
    }

    /** Promote only the head of an idle destination; a late vehicle never overlaps it. */
    public function advanceBoarding(?int $now = null): array
    {
        $now ??= time();
        $this->db->transStart();
        if (str_contains(strtolower($this->db->DBDriver), 'postgre')) {
            $key = crc32('queue_recalc_0');
            $this->db->query('SELECT pg_advisory_xact_lock(?)', [$key > 2147483647 ? $key - 4294967296 : $key]);
        }
        $this->resetForNewDay($now);
        $items = $this->select('queue.*, routes.terminal_id, routes.destination')
            ->join('routes', 'routes.id = queue.route_id')->whereIn('queue.status', ['waiting', 'boarding'])
            ->orderBy("CASE WHEN queue.status = 'boarding' THEN 0 ELSE 1 END", 'ASC')
            ->orderBy('queue.position', 'ASC')->orderBy('queue.id', 'ASC')->findAll();
        $seen = [];
        $promoted = [];
        foreach ($items as $item) {
            $key = $item['terminal_id'] . '|' . $item['destination'];
            if (isset($seen[$key])) continue;
            $seen[$key] = true;
            if ($item['status'] === 'waiting' && !empty($item['boarding_start']) && strtotime($item['boarding_start']) <= $now) {
                $this->update($item['id'], ['status' => 'boarding']);
                $promoted[] = (int) $item['id'];
            }
        }
        $this->db->transComplete();
        return $this->db->transStatus() ? $promoted : [];
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
