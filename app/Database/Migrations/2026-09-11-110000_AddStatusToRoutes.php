<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatusToRoutes extends Migration
{
    public function up()
    {
        $this->forge->addColumn('routes', [
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'active',
                'null'       => false,
            ],
        ]);

        $this->db->query("CREATE INDEX IF NOT EXISTS idx_routes_status ON routes(status)");
    }

    public function down()
    {
        $this->db->query("DROP INDEX IF EXISTS idx_routes_status");
        $this->forge->dropColumn('routes', 'status');
    }
}
