<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVehicleTypeToRoutes extends Migration
{
    public function up()
    {
        $fields = [
            'vehicle_type' => [
                'type'       => 'ENUM',
                'constraint' => ['jeepney', 'van'],
                'default'    => 'van',
                'after'      => 'fare'
            ],
        ];
        $this->forge->addColumn('routes', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('routes', 'vehicle_type');
    }
}
