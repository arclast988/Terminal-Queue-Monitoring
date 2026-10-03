<?php

namespace App\Models;

use CodeIgniter\Model;

class RouteModel extends Model
{
    protected $table            = 'routes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['destination', 'terminal_id', 'vehicle_type', 'status'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    /**
     * All vehicle-type routes sharing one terminal/destination queue line.
     * Departure rules use a representative route ID for this whole line.
     */
    public function getDestinationRouteIds(int $terminalId, string $destination): array
    {
        return array_map('intval', $this->where('terminal_id', $terminalId)
            ->where('destination', $destination)
            ->orderBy('id', 'ASC')
            ->findColumn('id') ?: []);
    }

    /**
     * Scope: join terminals to get origin name.
     * Usage: $routeModel->withOrigin()->findAll()
     */
    public function withOrigin(): self
    {
        return $this->select('routes.*, terminals.name as origin')
                    ->join('terminals', 'terminals.id = routes.terminal_id');
    }

    /**
     * Scope: Only active routes that have an active regular fare > 0.
     * Usage: $routeModel->withActiveFare()->findAll()
     */
    public function withActiveFare(): self
    {
        $db = \Config\Database::connect();
        $subquery = $db->table('fares')
            ->select('fares.route_id')
            ->join('fare_discounts', 'fare_discounts.id = fares.fare_discount_id')
            ->where('fare_discounts.type', 'regular')
            ->where('fares.amount >', 0);

        return $this->select('routes.*, terminals.name as origin')
                    ->join('terminals', 'terminals.id = routes.terminal_id')
                    ->where('routes.status', 'active')
                    ->whereIn('routes.id', $subquery);
    }

    /**
     * Scope: Active routes that do not have an active regular fare assigned.
     * Usage: $routeModel->withoutFare()->findAll()
     */
    public function withoutFare(): self
    {
        $db = \Config\Database::connect();
        $subquery = $db->table('fares')
            ->select('fares.route_id')
            ->join('fare_discounts', 'fare_discounts.id = fares.fare_discount_id')
            ->where('fare_discounts.type', 'regular')
            ->where('fares.amount >', 0);

        return $this->select('routes.*, terminals.name as origin')
                    ->join('terminals', 'terminals.id = routes.terminal_id')
                    ->where('routes.status', 'active')
                    ->whereNotIn('routes.id', $subquery);
    }
}
