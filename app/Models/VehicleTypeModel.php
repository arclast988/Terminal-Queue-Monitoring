<?php

namespace App\Models;

use CodeIgniter\Model;

class VehicleTypeModel extends Model
{
    protected $table = 'vehicle_types';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['name', 'slug', 'color', 'icon', 'is_active'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
