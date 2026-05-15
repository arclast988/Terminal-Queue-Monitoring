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
     * Get the wait minutes for a given time (H:i:s format).
     * Returns the matching rule's wait_minutes, or 30 as default.
     */
    public function getWaitMinutesForTime(string $time): int
    {
        $rule = $this->where('time_from <=', $time)
            ->where('time_to >', $time)
            ->first();

        return $rule ? (int) $rule['wait_minutes'] : 30;
    }
}
