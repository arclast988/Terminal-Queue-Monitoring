<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\QueueModel;

class RecalculateQueue extends BaseCommand
{
    protected $group       = 'Queue';
    protected $name        = 'queue:recalculate';
    protected $description = 'Recalculates per-route sequential estimated departure times and positions for active queue items.';

    public function run(array $params)
    {
        $queueModel = new QueueModel();
        $queueModel->recalculateSchedule();
        CLI::write('Queue schedules successfully recalculated.', 'green');
    }
}
