<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddOperatorNameToVehicles extends Migration
{
    public function up()
    {
        // Add operator_name column after driver_name
        $this->forge->addColumn('vehicles', [
            'operator_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'driver_name',
            ],
        ]);

        // Backfill existing rows so no vehicle is ever left without an operator
        $this->db->query("UPDATE vehicles SET operator_name = driver_name WHERE operator_name IS NULL AND driver_name IS NOT NULL AND driver_name != ''");
        $this->db->query("UPDATE vehicles SET operator_name = owner_name WHERE operator_name IS NULL AND owner_name IS NOT NULL AND owner_name != ''");
    }

    public function down()
    {
        $this->forge->dropColumn('vehicles', 'operator_name');
    }
}