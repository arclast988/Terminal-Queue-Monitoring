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
     * Returns an array of route_id values, e.g. [1, 3, 5].
     */
    public function getRouteIdsForUser(int $userId): array
    {
        $rows = $this->where('user_id', $userId)->findAll();
        return array_column($rows, 'route_id');
    }

    /**
     * Get full route records assigned to a user (joined with routes table).
     */
    public function getRoutesForUser(int $userId): array
    {
        return $this->select('routes.*')
            ->join('routes', 'routes.id = user_routes.route_id')
            ->where('user_routes.user_id', $userId)
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
        return implode(', ', $labels);
    }
}
