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
    protected $allowedFields    = ['destination', 'terminal_id', 'vehicle_type'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    /**
     * Scope: join terminals to get origin name.
     * Usage: $routeModel->withOrigin()->findAll()
     */
    public function withOrigin(): self
    {
        return $this->select('routes.*, terminals.name as origin')
                    ->join('terminals', 'terminals.id = routes.terminal_id');
    }
}
