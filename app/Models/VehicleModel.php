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
    protected $allowedFields    = ['plate_number', 'driver_name', 'operator_name', 'owner_name', 'type', 'capacity', 'status', 'default_route_id', 'scheduled_departure_time'];

    // Dates — vehicles table only has created_at, no updated_at
    protected $useTimestamps = false;

    protected $afterFind    = ['mapRouteIdAliases'];
    protected $beforeInsert = ['mapRouteIdOnWrite'];
    protected $beforeUpdate = ['mapRouteIdOnWrite'];

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
