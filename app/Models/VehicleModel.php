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
    protected $allowedFields    = ['plate_number', 'driver_name', 'owner_name', 'type', 'capacity', 'default_route_id', 'scheduled_departure_time', 'status'];

    // Dates — vehicles table only has created_at, no updated_at
    protected $useTimestamps = false;
    protected $createdField  = 'created_at';
}
