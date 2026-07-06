<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDefaultRouteToVehicles extends Migration
{
    public function up()
    {
        $this->forge->addColumn('vehicles', [
            'default_route_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'type'
            ],
        ]);

        $this->forge->addForeignKey('default_route_id', 'routes', 'id', 'SET NULL', 'CASCADE');
        // Note: CodeIgniter 4 doesn't support adding foreign keys to existing tables via addForeignKey in easy way in migrations.
        // It's better to use direct query if needed, or forge usually handles it if we use it correctly.
        // Actually, for adding a foreign key to an existing table, we might need to use direct query.
    }

    public function down()
    {
        $this->forge->dropColumn('vehicles', 'default_route_id');
    }
}
