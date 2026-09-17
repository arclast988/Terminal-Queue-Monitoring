<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\QueueModel;
use App\Models\LogModel;

class MaintenancePurge extends BaseCommand
{
    protected $group       = 'Maintenance';
    protected $name        = 'maintenance:purge';
    protected $description = 'Purges departed queue records and audit logs older than the specified retention days (default: 60 days).';
    protected $usage       = 'maintenance:purge [days]';
    protected $arguments   = [
        'days' => 'Number of retention days to keep before purging (default: 60)',
    ];

    public function run(array $params)
    {
        $depDays = isset($params[0]) && is_numeric($params[0]) ? (int) $params[0] : departure_retention_days();
        $logDays = isset($params[0]) && is_numeric($params[0]) ? (int) $params[0] : log_retention_days();

        CLI::write("Starting maintenance purge (Departures older than {$depDays} days, Audit logs older than {$logDays} days)...", 'yellow');

        $queueModel = new QueueModel();
        $deletedTrips = $queueModel->purgeOldDepartures($depDays);
        CLI::write("✓ Purged {$deletedTrips} departed queue record(s).", 'green');

        $logModel = new LogModel();
        $deletedLogs = $logModel->purgeOldLogs($logDays);
        CLI::write("✓ Purged {$deletedLogs} audit log record(s).", 'green');

        CLI::write('Maintenance purge completed successfully.', 'light_green');
    }
}
