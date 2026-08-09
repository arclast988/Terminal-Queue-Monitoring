<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVehicleTypes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 80],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 50],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at DATETIME DEFAULT CURRENT_TIMESTAMP',
            'updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->createTable('vehicle_types', true);

        foreach ([
            ['name' => 'Van', 'slug' => 'van'],
            ['name' => 'Jeepney', 'slug' => 'jeepney'],
            ['name' => 'Minibus', 'slug' => 'minibus'],
        ] as $type) {
            $this->db->table('vehicle_types')->ignore(true)->insert($type);
        }

        // These columns started as ENUMs, which prevents administrators from
        // adding a new vehicle type. The configured type slug is now stored.
        $this->db->query('ALTER TABLE `vehicles` MODIFY `type` VARCHAR(50) NOT NULL');
        $this->db->query("ALTER TABLE `routes` MODIFY `vehicle_type` VARCHAR(50) NOT NULL DEFAULT 'van'");
    }

    public function down()
    {
        $this->db->query("ALTER TABLE `vehicles` MODIFY `type` ENUM('jeepney','van','minibus') NOT NULL");
        $this->db->query("ALTER TABLE `routes` MODIFY `vehicle_type` ENUM('jeepney','van','minibus') NOT NULL DEFAULT 'van'");
        $this->forge->dropTable('vehicle_types', true);
    }
}
