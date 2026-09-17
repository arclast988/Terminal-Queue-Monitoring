<?php

namespace App\Models;

use CodeIgniter\Model;

class LogModel extends Model
{
    protected $table            = 'audit_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'action', 'details', 'timestamp'];

    // Dates
    protected $useTimestamps = false; // Manually handling timestamp

    /**
     * Automatically purge logs older than the specified retention days.
     *
     * @param int|null $days Number of days to retain logs (defaults to system setting log_retention_days())
     * @return int Number of deleted rows
     */
    public function purgeOldLogs(?int $days = null): int
    {
        $days = ($days !== null && $days >= 1) ? $days : log_retention_days();
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        return (int) $this->where('timestamp <', $cutoff)->delete();
    }
}
