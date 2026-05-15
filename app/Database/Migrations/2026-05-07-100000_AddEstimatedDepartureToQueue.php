<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds queue.estimated_departure (DATETIME, nullable).
 *
 * Background: this column is heavily used by the application
 * (Home, Schedules, Search, Staff\Queue, public views) but was
 * never captured in a migration — only added directly to the live
 * database. This migration backfills that gap so a fresh
 * `php spark migrate` produces a working schema.
 *
 * Idempotent: only adds the column if it does not already exist,
 * so it is safe to run against the live DB where the column is
 * already present.
 */
class AddEstimatedDepartureToQueue extends Migration
{
    public function up()
    {
        if (! $this->columnExists('queue', 'estimated_departure')) {
            $this->forge->addColumn('queue', [
                'estimated_departure' => [
                    'type'  => 'DATETIME',
                    'null'  => true,
                    'after' => 'arrival_time',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->columnExists('queue', 'estimated_departure')) {
            $this->forge->dropColumn('queue', 'estimated_departure');
        }
    }

    private function columnExists(string $table, string $column): bool
    {
        $db = \Config\Database::connect();
        return $db->fieldExists($column, $table);
    }
}
