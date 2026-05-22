<?php

namespace App\Models;

use CodeIgniter\Model;

class DepartureRuleModel extends Model
{
    protected $table = 'departure_rules';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['terminal_id', 'time_from', 'time_to', 'wait_minutes', 'label'];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get the matching departure rule for a given time and terminal.
     * Returns the full rule array, or a default fallback if no rule matches.
     */
    public function getRuleForTime(string $time, int $terminalId): array
    {
        $rule = $this->where('terminal_id', $terminalId)
            ->where('time_from <=', $time)
            ->where('time_to >', $time)
            ->first();

        if ($rule) {
            return $rule;
        }

        // Fallback: no matching rule
        return [
            'wait_minutes' => 30,
            'label' => 'Default (no rule matched)',
            'time_from' => null,
            'time_to' => null,
        ];
    }

    /**
     * Get the wait minutes for a given time and terminal.
     */
    public function getWaitMinutesForTime(string $time, int $terminalId): int
    {
        $rule = $this->getRuleForTime($time, $terminalId);
        return (int) $rule['wait_minutes'];
    }
}
