<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates the departure_rules table used by DepartureRuleModel and
 * referenced from the Schedules controller and Staff\Queue logic.
 *
 * Background: the table was originally created directly in the live
 * database (see jeepneynvans.sql) but no migration ever existed for
 * it. A fresh `php spark migrate` would leave the application broken
 * without this file.
 *
 * Idempotent: uses CREATE TABLE IF NOT EXISTS, so it is safe to run
 * against the live DB where the table is already populated.
 */
class CreateDepartureRulesTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if ($db->tableExists('departure_rules')) {
            return;
        }

        $sql = "CREATE TABLE IF NOT EXISTS `departure_rules` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `time_from` TIME NOT NULL,
            `time_to` TIME NOT NULL,
            `wait_minutes` INT(11) NOT NULL DEFAULT 30,
            `label` VARCHAR(50) DEFAULT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $db->query($sql);

        // Seed the default time-bracketed rules used by the system.
        // Skipped if any rows already exist (additional safety on top of tableExists check).
        $count = $db->table('departure_rules')->countAllResults();
        if ($count === 0) {
            $db->table('departure_rules')->insertBatch([
                ['time_from' => '00:00:00', 'time_to' => '05:00:00', 'wait_minutes' => 60, 'label' => 'Late Night / Early Morning'],
                ['time_from' => '05:00:00', 'time_to' => '09:00:00', 'wait_minutes' => 30, 'label' => 'Morning Rush'],
                ['time_from' => '09:00:00', 'time_to' => '12:00:00', 'wait_minutes' => 40, 'label' => 'Mid-Morning'],
                ['time_from' => '12:00:00', 'time_to' => '15:00:00', 'wait_minutes' => 40, 'label' => 'Afternoon'],
                ['time_from' => '15:00:00', 'time_to' => '18:00:00', 'wait_minutes' => 30, 'label' => 'Afternoon Rush'],
                ['time_from' => '18:00:00', 'time_to' => '21:00:00', 'wait_minutes' => 40, 'label' => 'Evening'],
                ['time_from' => '21:00:00', 'time_to' => '23:59:59', 'wait_minutes' => 60, 'label' => 'Late Night'],
            ]);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('departure_rules')) {
            $this->forge->dropTable('departure_rules');
        }
    }
}
