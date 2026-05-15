<?php

namespace App\Models;

use CodeIgniter\Model;

class FareModel extends Model
{
    protected $table            = 'fares';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['route_id', 'amount'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function upsertForRoute(int $routeId, $amount): bool
    {
        $existing = $this->where('route_id', $routeId)->first();

        if ($existing) {
            return (bool) $this->update($existing['id'], ['amount' => $amount]);
        }

        return (bool) $this->insert([
            'route_id' => $routeId,
            'amount'   => $amount,
        ]);
    }

    public function amountForRoute(int $routeId): ?string
    {
        $fare = $this->where('route_id', $routeId)->first();

        return $fare['amount'] ?? null;
    }
}
