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
        $days = isset($params[0]) && is_numeric($params[0]) ? (int) $params[0] : 60;
        if ($days < 1) {
            $days = 60;
        }

        CLI::write("Starting maintenance purge for records older than {$days} days...", 'yellow');

        $queueModel = new QueueModel();
        $deletedTrips = $queueModel->purgeOldDepartures($days);
        CLI::write("✓ Purged {$deletedTrips} departed queue record(s).", 'green');

        $logModel = new LogModel();
        $deletedLogs = $logModel->purgeOldLogs($days);
        CLI::write("✓ Purged {$deletedLogs} audit log record(s).", 'green');

        CLI::write('Maintenance purge completed successfully.', 'light_green');
    }
}
