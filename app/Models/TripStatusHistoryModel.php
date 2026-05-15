<?php

namespace App\Models;

use CodeIgniter\Model;

class TripStatusHistoryModel extends Model
{
    protected $table            = 'trip_status_history';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['queue_id', 'status', 'timestamp', 'updated_by_user_id'];

    // Dates
    protected $useTimestamps = false; // Manually handling timestamp
}
