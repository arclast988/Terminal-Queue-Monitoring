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
}
