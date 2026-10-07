<?php

namespace App\Models;

use CodeIgniter\Model;

class VehicleModel extends Model
{
    protected $table            = 'vehicles';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['plate_number', 'driver_name', 'operator_name', 'owner_name', 'type', 'capacity', 'status', 'default_route_id', 'scheduled_departure_time', 'photo', 'created_at', 'dispatch_order', 'dispatch_rotation', 'dispatch_rotation_date'];

    // Dates — vehicles table has created_at
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    protected $afterFind    = ['mapRouteIdAliases', 'uppercaseFieldsOnRead'];
    protected $beforeInsert = ['mapRouteIdOnWrite', 'uppercaseFieldsOnWrite'];
    protected $beforeUpdate = ['mapRouteIdOnWrite', 'uppercaseFieldsOnWrite'];

    /** Save one position and shift its neighbours within the destination queue line. */
    public function saveInDispatchOrder(array $data, ?int $id = null): bool
    {
        $this->db->transStart();
        $this->lockDispatchOrder();
        $this->lockAllDispatchRoutes();
        $old = $id ? $this->find($id) : null;
        $routeId = (int) ($data['default_route_id'] ?? $old['default_route_id'] ?? 0);
        $routeIds = $this->dispatchRouteIds($routeId);
        $oldRouteIds = $old ? $this->dispatchRouteIds((int) $old['default_route_id']) : [];
        $this->lockRoutes(array_merge($routeIds, $oldRouteIds));
        $ids = $this->dispatchVehicleIds($routeIds, $id);
        $requested = $data['dispatch_order'] ?? null;
        $position = ($requested === null || $requested === '') ? count($ids) + 1 : max(1, min((int) $requested, count($ids) + 1));
        $data['dispatch_order'] = $position;
        if ($oldRouteIds !== $routeIds) {
            $data['dispatch_rotation'] = 0;
            $data['dispatch_rotation_date'] = null;
        }
        $saved = $id ? $this->update($id, $data) : $this->insert($data) !== false;
        if ($saved) {
            $id ??= (int) $this->getInsertID();
            array_splice($ids, $position - 1, 0, [$id]);
            $this->writeDispatchPositions($ids);
            if ($oldRouteIds && $oldRouteIds !== $routeIds) $this->writeDispatchPositions($this->dispatchVehicleIds($oldRouteIds, $id));
        }
        $this->db->transComplete();
        return $saved && $this->db->transStatus();
    }

    /** Called in the same transaction as departure; each departure gets a unique turn. */
    public function rotateAfterDeparture(int $id, int $routeId, ?int $now = null): void
    {
        $now ??= time();
        $this->db->transStart();
        $this->lockDispatchOrder();
        $this->lockAllDispatchRoutes();
        $routeIds = $this->dispatchRouteIds($routeId);
        $this->lockRoutes($routeIds);
        $vehicle = $this->find($id);
        if ($vehicle && in_array((int) $vehicle['default_route_id'], $routeIds, true)) {
            $today = date('Y-m-d', $now);
            $last = $this->db->table('vehicles')->selectMax('dispatch_rotation')
                ->whereIn('default_route_id', $routeIds)->where('dispatch_rotation_date', $today)->get()->getRowArray();
            $this->update($id, ['dispatch_rotation' => (int) ($last['dispatch_rotation'] ?? 0) + 1, 'dispatch_rotation_date' => $today]);
        }
        $this->db->transComplete();
    }

    /** Saved starting order, then today's departure order, independently per route line. */
    public static function sortForDispatch(array $vehicles, string $today): array
    {
        usort($vehicles, static function (array $a, array $b) use ($today): int {
            $destination = strcmp((string) ($a['route_destination'] ?? ''), (string) ($b['route_destination'] ?? ''));
            if ($destination !== 0) return $destination;
            $terminal = (int) ($a['route_terminal_id'] ?? 0) <=> (int) ($b['route_terminal_id'] ?? 0);
            if ($terminal !== 0) return $terminal;
            $aTurn = ($a['dispatch_rotation_date'] ?? '') === $today ? (int) ($a['dispatch_rotation'] ?? 0) : 0;
            $bTurn = ($b['dispatch_rotation_date'] ?? '') === $today ? (int) ($b['dispatch_rotation'] ?? 0) : 0;
            $departed = ($aTurn > 0) <=> ($bTurn > 0);
            if ($departed !== 0) return $departed;
            if ($aTurn > 0 && $aTurn !== $bTurn) return $aTurn <=> $bTurn;
            $position = ((int) ($a['dispatch_order'] ?? 0) ?: PHP_INT_MAX) <=> ((int) ($b['dispatch_order'] ?? 0) ?: PHP_INT_MAX);
            if ($position !== 0) return $position;
            $type = strcmp((string) ($a['type'] ?? ''), (string) ($b['type'] ?? ''));
            if ($type !== 0) return $type;
            $plate = strcmp((string) ($a['plate_number'] ?? ''), (string) ($b['plate_number'] ?? ''));
            return $plate !== 0 ? $plate : (int) $a['id'] <=> (int) $b['id'];
        });
        $positions = [];
        foreach ($vehicles as &$vehicle) {
            $key = ($vehicle['route_terminal_id'] ?? 0) . '|' . ($vehicle['route_destination'] ?? '');
            $positions[$key] = ($positions[$key] ?? 0) + 1;
            $vehicle['dispatch_position'] = $positions[$key];
        }
        unset($vehicle);
        return $vehicles;
    }

    /** Register order uses the same daily rotation, with unavailable vehicles unnumbered. */
    public static function sortForRegister(array $vehicles, string $today): array
    {
        $groups = [];
        foreach (self::sortForDispatch($vehicles, $today) as $vehicle) {
            $key = !empty($vehicle['route_destination'])
                ? ($vehicle['route_terminal_id'] ?? 0) . '|' . $vehicle['route_destination']
                : '__none';
            $available = $key !== '__none' && ($vehicle['status'] ?? '') === 'active';
            $groups[$key][$available ? 'active' : 'unavailable'][] = $vehicle;
        }
        if (isset($groups['__none'])) {
            $unassigned = $groups['__none'];
            unset($groups['__none']);
            $groups['__none'] = $unassigned;
        }
        $sorted = [];
        foreach ($groups as $group) {
            foreach ($group['active'] ?? [] as $index => $vehicle) {
                $vehicle['dispatch_position'] = $index + 1;
                $sorted[] = $vehicle;
            }
            foreach ($group['unavailable'] ?? [] as $vehicle) {
                $vehicle['dispatch_position'] = null;
                $sorted[] = $vehicle;
            }
        }
        return $sorted;
    }

    /** Repair duplicates and gaps, including when route edits merge destination lines. */
    public function normalizeDispatchOrders(): void
    {
        if (!$this->db->tableExists('vehicles') || !$this->db->tableExists('routes')
            || !$this->db->fieldExists('dispatch_order', 'vehicles')) return;
        $this->db->transBegin();
        try {
            $this->lockDispatchOrder();
            $this->lockAllDispatchRoutes();
            $routes = $this->db->table('routes')->select('id, terminal_id, destination')
                ->orderBy('id', 'ASC')->get()->getResultArray();
            $this->lockRoutes(array_column($routes, 'id'));
            $scopes = [];
            foreach ($routes as $route) {
                $scopes[$route['terminal_id'] . '|' . $route['destination']][] = (int) $route['id'];
            }
            foreach ($scopes as $routeIds) {
                $rows = $this->db->table('vehicles')->select('id, dispatch_order')
                    ->whereIn('default_route_id', $routeIds)
                    ->orderBy('CASE WHEN dispatch_order > 0 THEN dispatch_order ELSE 2147483647 END', 'ASC', false)
                    ->orderBy('type', 'ASC')->orderBy('plate_number', 'ASC')->orderBy('id', 'ASC')
                    ->get()->getResultArray();
                foreach ($rows as $index => $row) {
                    if ((int) $row['dispatch_order'] !== $index + 1) {
                        $this->db->table('vehicles')->where('id', $row['id'])
                            ->update(['dispatch_order' => $index + 1]);
                    }
                }
            }
            if (!$this->db->transStatus()) throw new \RuntimeException('Could not normalize vehicle order.');
            $this->db->transCommit();
        } catch (\Throwable $error) {
            $this->db->transRollback();
            throw $error;
        }
    }

    private function lockDispatchOrder(): void
    {
        if (str_contains(strtolower($this->db->DBDriver), 'postgre')) {
            $key = crc32('queue_recalc_0');
            $this->db->query('SELECT pg_advisory_xact_lock(?)', [$key > 2147483647 ? $key - 4294967296 : $key]);
        }
    }

    private function lockRoutes(array $routeIds): void
    {
        $ids = array_values(array_unique(array_map('intval', $routeIds)));
        sort($ids);
        if ($ids && str_contains(strtolower($this->db->DBDriver), 'mysql')) {
            $this->db->query('SELECT id FROM routes WHERE id IN (' . implode(',', $ids) . ') ORDER BY id FOR UPDATE');
        }
    }

    /** Lock before reading destinations so a concurrent route rename cannot leave stale scopes. */
    private function lockAllDispatchRoutes(): void
    {
        if (str_contains(strtolower($this->db->DBDriver), 'mysql')) {
            $this->db->query('SELECT id FROM routes ORDER BY id FOR UPDATE');
        }
    }

    private function dispatchRouteIds(int $routeId): array
    {
        $routes = new RouteModel($this->db);
        $route = $routes->find($routeId);
        return $route ? $routes->getDestinationRouteIds((int) $route['terminal_id'], $route['destination']) : [];
    }

    private function dispatchVehicleIds(array $routeIds, ?int $exclude): array
    {
        if (!$routeIds) return [];
        $builder = $this->db->table('vehicles')->select('id')->whereIn('default_route_id', $routeIds);
        if ($exclude) $builder->where('id !=', $exclude);
        return array_map('intval', array_column($builder->orderBy('dispatch_order', 'ASC')
            ->orderBy('type', 'ASC')->orderBy('plate_number', 'ASC')->orderBy('id', 'ASC')->get()->getResultArray(), 'id'));
    }

    private function writeDispatchPositions(array $ids): void
    {
        foreach ($ids as $index => $id) $this->db->table('vehicles')->where('id', $id)->update(['dispatch_order' => $index + 1]);
    }

    /**
     * Automatically ensure plate_number, operator_name, and owner_name are uppercase on write.
     */
    protected function uppercaseFieldsOnWrite(array $data): array
    {
        if (isset($data['data']['plate_number'])) {
            $data['data']['plate_number'] = strtoupper(trim((string)$data['data']['plate_number']));
        }
        if (isset($data['data']['operator_name'])) {
            $data['data']['operator_name'] = strtoupper(trim((string)$data['data']['operator_name']));
        }
        if (isset($data['data']['owner_name'])) {
            $data['data']['owner_name'] = strtoupper(trim((string)$data['data']['owner_name']));
        }
        return $data;
    }

    /**
     * Ensure plate_number, operator_name, and owner_name are uppercase on read across all views and queries.
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
            if (isset($data['data']['owner_name'])) {
                $data['data']['owner_name'] = strtoupper((string)$data['data']['owner_name']);
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
                if (isset($row['owner_name'])) {
                    $row['owner_name'] = strtoupper((string)$row['owner_name']);
                }
            }
        }

        return $data;
    }

    /**
     * Ensures both default_route_id and route_id are present on fetched records,
     * maintaining 100% backwards-compatibility across all controllers and views.
     */
    protected function mapRouteIdAliases(array $data): array
    {
        if (empty($data['data'])) {
            return $data;
        }

        if (!empty($data['singleton'])) {
            $data['data'] = $this->ensureAliases($data['data']);
            return $data;
        }

        foreach ($data['data'] as &$row) {
            if (is_array($row)) {
                $row = $this->ensureAliases($row);
            }
        }

        return $data;
    }

    private function ensureAliases(array $row): array
    {
        if (isset($row['default_route_id']) && !isset($row['route_id'])) {
            $row['route_id'] = $row['default_route_id'];
        } elseif (isset($row['route_id']) && !isset($row['default_route_id'])) {
            $row['default_route_id'] = $row['route_id'];
        }
        return $row;
    }

    /**
     * If caller passes 'route_id' on insert/update, map it to 'default_route_id'.
     */
    protected function mapRouteIdOnWrite(array $data): array
    {
        if (isset($data['data']['route_id']) && !isset($data['data']['default_route_id'])) {
            $data['data']['default_route_id'] = $data['data']['route_id'];
        }
        // Strip virtual alias so only the real column is written.
        unset($data['data']['route_id']);
        return $data;
    }
}
