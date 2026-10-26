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
 * Idempotent: uses createTable with IF NOT EXISTS, so it is safe to
 * run against the live DB where the table is already populated.
 */
class CreateDepartureRulesTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if ($db->tableExists('departure_rules')) {
            return;
        }

        $this->forge->addField([
            'id'           => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'time_from'    => ['type' => 'TIME', 'null' => false],
            'time_to'      => ['type' => 'TIME', 'null' => false],
            'wait_minutes' => ['type' => 'INT', 'constraint' => 11, 'default' => 30],
            'label'        => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'created_at'   => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at'   => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('departure_rules', true);

        // Seed the default time-bracketed rules used by the system.
        // Skipped if any rows already exist (additional safety on top of tableExists check).
        $count = $db->table('departure_rules')->countAllResults();
        if ($count === 0) {
            $db->table('departure_rules')->insertBatch(\App\Libraries\DepartureRuleDefaults::rules());
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
