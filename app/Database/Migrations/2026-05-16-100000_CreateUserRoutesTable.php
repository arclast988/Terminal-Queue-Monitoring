<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates the user_routes linking table for dispatcher route assignments.
 * Only staff (dispatcher) users need entries here; admin users have access to all routes.
 */
class CreateUserRoutesTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if ($db->tableExists('user_routes')) {
            return;
        }

        $this->forge->addField([
            'id'         => ['type' => 'INT', 'auto_increment' => true],
            'user_id'    => ['type' => 'INT', 'null' => false],
            'route_id'   => ['type' => 'INT', 'null' => false],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('route_id', 'routes', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addUniqueKey(['user_id', 'route_id'], 'unique_user_route');
        $this->forge->createTable('user_routes');
    }

    public function down()
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('user_routes')) {
            $this->forge->dropTable('user_routes');
        }
    }
}
