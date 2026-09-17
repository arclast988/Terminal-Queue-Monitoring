<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateVehicleTypeIconsAndColors extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('vehicle_types')) {
            $types = [
                'van'        => ['icon' => 'fa-van-shuttle', 'color' => '#c62828'],
                'jeepney'    => ['icon' => 'fa-truck-front', 'color' => '#1565c0'],
                'minibus'    => ['icon' => 'fa-bus',         'color' => '#2e7d32'],
                'bus'        => ['icon' => 'fa-bus-simple',  'color' => '#ea580c'],
                'tricycle'   => ['icon' => 'fa-motorcycle',  'color' => '#7c3aed'],
                'motorcycle' => ['icon' => 'fa-motorcycle',  'color' => '#059669'],
                'taxi'       => ['icon' => 'fa-taxi',        'color' => '#ca8a04'],
                'car'        => ['icon' => 'fa-car',         'color' => '#0891b2'],
            ];

            foreach ($types as $slug => $data) {
                $this->db->table('vehicle_types')
                    ->where('slug', $slug)
                    ->update($data);
            }
        }
    }

    public function down()
    {
        // No-op
    }
}
