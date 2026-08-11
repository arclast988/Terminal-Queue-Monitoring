<?php

namespace App\Models;

use CodeIgniter\Model;

class FareModel extends Model
{
    protected $table            = 'fares';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['route_id', 'fare_discount_id', 'amount'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
