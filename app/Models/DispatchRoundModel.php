<?php

namespace App\Models;

use CodeIgniter\Model;

class DispatchRoundModel extends Model
{
    protected $table = 'dispatch_rounds';
    protected $returnType = 'array';
    protected $allowedFields = ['terminal_id', 'destination', 'service_date', 'round_number'];

    public function currentRound(int $terminalId, string $destination): int
    {
        $state = $this->where('terminal_id', $terminalId)->where('destination', $destination)->first();
        return ($state && $state['service_date'] === date('Y-m-d')) ? (int) $state['round_number'] : 1;
    }

    public function setRound(int $terminalId, string $destination, int $round): void
    {
        $state = $this->where('terminal_id', $terminalId)->where('destination', $destination)->first();
        $data = ['terminal_id' => $terminalId, 'destination' => $destination,
            'service_date' => date('Y-m-d'), 'round_number' => $round];
        if ($state) {
            $this->update($state['id'], $data);
        } else {
            $this->insert($data);
        }
    }
}
