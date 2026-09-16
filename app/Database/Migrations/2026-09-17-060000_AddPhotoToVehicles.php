<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPhotoToVehicles extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('vehicles')) {
            if (! $this->db->fieldExists('photo', 'vehicles')) {
                $this->forge->addColumn('vehicles', [
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
        if ($this->db->tableExists('vehicles')) {
            if ($this->db->fieldExists('photo', 'vehicles')) {
                $this->forge->dropColumn('vehicles', 'photo');
            }
        }
    }
}
