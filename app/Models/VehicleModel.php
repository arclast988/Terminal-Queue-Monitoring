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
    protected $allowedFields    = ['plate_number', 'driver_name', 'operator_name', 'owner_name', 'type', 'capacity', 'status', 'default_route_id', 'scheduled_departure_time', 'photo', 'created_at'];

    // Dates — vehicles table has created_at
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    protected $afterFind    = ['mapRouteIdAliases', 'uppercaseFieldsOnRead'];
    protected $beforeInsert = ['mapRouteIdOnWrite', 'uppercaseFieldsOnWrite'];
    protected $beforeUpdate = ['mapRouteIdOnWrite', 'uppercaseFieldsOnWrite'];

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
