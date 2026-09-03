<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddColorAndIconToVehicleTypes extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('vehicle_types')) {
            $fields = [];
            if (! $this->db->fieldExists('color', 'vehicle_types')) {
                $fields['color'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'default'    => '#0284c7',
                    'null'       => true,
                ];
            }
            if (! $this->db->fieldExists('icon', 'vehicle_types')) {
                $fields['icon'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'default'    => 'fa-bus',
                    'null'       => true,
                ];
            }
            if (! empty($fields)) {
                $this->forge->addColumn('vehicle_types', $fields);
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('vehicle_types')) {
            if ($this->db->fieldExists('color', 'vehicle_types')) {
                $this->forge->dropColumn('vehicle_types', 'color');
            }
            if ($this->db->fieldExists('icon', 'vehicle_types')) {
                $this->forge->dropColumn('vehicle_types', 'icon');
            }
        }
    }
}
