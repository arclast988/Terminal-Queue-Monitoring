<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MoveRouteFareToFaresTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if (!$db->tableExists('fares')) {
            $this->forge->addField([
                'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
                'route_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'amount'     => ['type' => 'DECIMAL', 'constraint' => '10,2'],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('route_id');
            $this->forge->addForeignKey('route_id', 'routes', 'id', 'CASCADE', 'CASCADE');
            $this->forge->createTable('fares');
        }

        if ($db->fieldExists('fare', 'routes')) {
            $db->query("
                INSERT INTO fares (route_id, amount, created_at, updated_at)
                SELECT id, fare, COALESCE(created_at, NOW()), NOW()
                FROM routes
                ON DUPLICATE KEY UPDATE amount = VALUES(amount), updated_at = VALUES(updated_at)
            ");

            $this->forge->dropColumn('routes', 'fare');
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        if (!$db->fieldExists('fare', 'routes')) {
            $this->forge->addColumn('routes', [
                'fare' => [
                    'type'       => 'DECIMAL',
                    'constraint' => '10,2',
                    'default'    => 0,
                    'after'      => 'destination',
                ],
            ]);
        }

        if ($db->tableExists('fares')) {
            $db->query('
                UPDATE routes
                JOIN fares ON fares.route_id = routes.id
                SET routes.fare = fares.amount
            ');

            $this->forge->dropTable('fares');
        }
    }
}
