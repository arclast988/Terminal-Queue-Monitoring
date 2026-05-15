<?php

namespace App\Models;

use CodeIgniter\Model;

class AnnouncementModel extends Model
{
    protected $table            = 'announcements';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement  = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes    = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['message', 'is_active', 'sort_order'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
