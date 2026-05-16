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
    protected $allowedFields = ['time_from', 'time_to', 'wait_minutes', 'label'];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get the matching departure rule for a given time (H:i:s format).
     * Returns the full rule array, or a default fallback if no rule matches.
     */
    public function getRuleForTime(string $time): array
    {
        $rule = $this->where('time_from <=', $time)
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
     * Get the wait minutes for a given time (H:i:s format).
     * Returns the matching rule's wait_minutes, or 30 as default.
     */
    public function getWaitMinutesForTime(string $time): int
    {
        $rule = $this->getRuleForTime($time);
        return (int) $rule['wait_minutes'];
    }
}
