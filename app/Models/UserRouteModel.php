<?php

namespace App\Models;

use CodeIgniter\Model;

class UserRouteModel extends Model
{
    protected $table            = 'user_routes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'route_id'];

    protected $useTimestamps = false;

    /**
     * Get assigned route IDs for a given user.
     * Returns an array of route_id values, e.g. [1, 3, 5, 12].
     * Automatically includes sibling routes (different vehicle types)
     * serving the same assigned destination and terminal.
     */
    public function getRouteIdsForUser(int $userId): array
    {
        $rows = $this->where('user_id', $userId)->findAll();
        $assignedRouteIds = array_column($rows, 'route_id');
        if (empty($assignedRouteIds)) {
            return [];
        }

        $db = \Config\Database::connect();
        $assignedPairs = $db->table('routes')
            ->select('terminal_id, destination')
            ->whereIn('id', $assignedRouteIds)
            ->get()
            ->getResultArray();

        if (empty($assignedPairs)) {
            return array_map('intval', $assignedRouteIds);
        }

        $builder = $db->table('routes')->select('id');
        $builder->groupStart();
        foreach ($assignedPairs as $i => $pair) {
            if ($i === 0) {
                $builder->where('terminal_id', $pair['terminal_id'])
                        ->where('destination', $pair['destination']);
            } else {
                $builder->orGroupStart()
                        ->where('terminal_id', $pair['terminal_id'])
                        ->where('destination', $pair['destination'])
                        ->groupEnd();
            }
        }
        $builder->groupEnd();

        $allMatchingRoutes = $builder->get()->getResultArray();
        $allIds = array_column($allMatchingRoutes, 'id');

        return !empty($allIds) ? array_map('intval', array_unique(array_merge($assignedRouteIds, $allIds))) : array_map('intval', $assignedRouteIds);
    }

    /**
     * Get full route records assigned to a user (joined with routes table).
     */
    public function getRoutesForUser(int $userId): array
    {
        $routeIds = $this->getRouteIdsForUser($userId);
        if (empty($routeIds)) {
            return [];
        }

        return (new RouteModel())->select('routes.*, terminals.name as origin')
            ->join('terminals', 'terminals.id = routes.terminal_id')
            ->whereIn('routes.id', $routeIds)
            ->orderBy('routes.destination', 'ASC')
            ->findAll();
    }

    /**
     * Sync route assignments: delete all existing, insert new set.
     * @param int   $userId   The user to sync routes for
     * @param array $routeIds Array of route_id values to assign
     */
    public function syncRoutesForUser(int $userId, array $routeIds): void
    {
        $db = \Config\Database::connect();
        $db->transStart();

        // Remove all existing assignments
        $this->where('user_id', $userId)->delete();

        // Insert new assignments
        if (!empty($routeIds)) {
            $batch = [];
            foreach ($routeIds as $routeId) {
                $batch[] = [
                    'user_id'  => $userId,
                    'route_id' => (int) $routeId,
                ];
            }
            $this->insertBatch($batch);
        }

        $db->transComplete();
    }

    /**
     * Get a formatted label for a user's assigned routes.
     * Returns "All Routes" for admin, or comma-separated route labels for staff.
     */
    public function getRouteLabelsForUser(int $userId): string
    {
        $routes = $this->getRoutesForUser($userId);
        if (empty($routes)) {
            return 'None';
        }
        $labels = [];
        foreach ($routes as $r) {
            $labels[] = strtoupper($r['origin']) . ' → ' . strtoupper($r['destination']);
        }
        return implode(', ', array_unique($labels));
    }

    /**
     * When a new route is created for a terminal and destination, automatically
     * assign it to any dispatchers who already manage that terminal + destination.
     */
    public function autoAssignNewRouteToStaff(int $newRouteId, int $terminalId, string $destination): void
    {
        $db = \Config\Database::connect();
        $siblingRoutes = $db->table('routes')
            ->select('id')
            ->where('terminal_id', $terminalId)
            ->where('destination', $destination)
            ->where('id !=', $newRouteId)
            ->get()
            ->getResultArray();

        if (empty($siblingRoutes)) {
            return;
        }

        $siblingIds = array_column($siblingRoutes, 'id');
        $staffUsers = $db->table('user_routes')
            ->select('user_id')
            ->whereIn('route_id', $siblingIds)
            ->groupBy('user_id')
            ->get()
            ->getResultArray();

        foreach ($staffUsers as $su) {
            $uId = (int) $su['user_id'];
            $exists = $this->where('user_id', $uId)->where('route_id', $newRouteId)->first();
            if (!$exists) {
                $this->insert([
                    'user_id'  => $uId,
                    'route_id' => $newRouteId,
                ]);
            }
        }
    }
}
