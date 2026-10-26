<?php

namespace App\Models;

use CodeIgniter\Model;

class TerminalModel extends Model
{
    protected $table            = 'terminals';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'location', 'capacity'];
    protected $beforeInsert     = ['normalizeName'];
    protected $beforeUpdate     = ['normalizeName'];

    protected function normalizeName(array $event): array
    {
        if (isset($event['data']['name'])) {
            $event['data']['name'] = mb_strtoupper(trim((string) $event['data']['name']), 'UTF-8');
        }
        return $event;
    }

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = ''; // No updated_at in schema for terminals
}
