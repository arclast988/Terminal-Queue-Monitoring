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
                'id'         => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
                'route_id'   => ['type' => 'INT', 'constraint' => 11],
                'amount'     => ['type' => 'DECIMAL', 'constraint' => '10,2'],
                'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
                'updated_at' => ['type' => 'TIMESTAMP', 'null' => true],
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
                ON CONFLICT (route_id) DO UPDATE SET amount = EXCLUDED.amount, updated_at = EXCLUDED.updated_at
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
                ],
            ]);
        }

        if ($db->tableExists('fares')) {
            $db->query('
                UPDATE routes
                SET fare = fares.amount
                FROM fares
                WHERE fares.route_id = routes.id
            ');

            $this->forge->dropTable('fares');
        }
    }
}
