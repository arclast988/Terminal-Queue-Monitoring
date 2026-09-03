<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVehicleTypeToRoutes extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('vehicle_type', 'routes')) {
            $fields = [
                'vehicle_type' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'default'    => 'van',
                    'after'      => 'fare'
                ],
            ];
            $this->forge->addColumn('routes', $fields);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('vehicle_type', 'routes')) {
            $this->forge->dropColumn('routes', 'vehicle_type');
        }
    }
}
