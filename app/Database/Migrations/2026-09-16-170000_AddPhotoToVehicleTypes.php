<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPhotoToVehicleTypes extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('vehicle_types')) {
            if (! $this->db->fieldExists('photo', 'vehicle_types')) {
                $this->forge->addColumn('vehicle_types', [
                    'photo' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 255,
                        'null'       => true,
                        'default'    => null,
                    ],
                ]);
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('vehicle_types')) {
            if ($this->db->fieldExists('photo', 'vehicle_types')) {
                $this->forge->dropColumn('vehicle_types', 'photo');
            }
        }
    }
}
