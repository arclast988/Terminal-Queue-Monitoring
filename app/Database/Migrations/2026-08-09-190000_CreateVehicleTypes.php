<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVehicleTypes extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 80],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 50],
            'is_active' => ['type' => 'SMALLINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at' => ['type' => 'TIMESTAMP', 'null' => true],
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
        $this->forge->modifyColumn('vehicles', [
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
        ]);
        $this->forge->modifyColumn('routes', [
            'vehicle_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'default'    => 'van',
            ],
        ]);
    }

    public function down()
    {
        // Restore column types via Forge (VARCHAR stays VARCHAR — the original
        // ENUM values are not restored since PostgreSQL doesn't support ENUM).
        $this->forge->modifyColumn('vehicles', [
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
        ]);
        $this->forge->modifyColumn('routes', [
            'vehicle_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'default'    => 'van',
            ],
        ]);
        $this->forge->dropTable('vehicle_types', true);
    }
}
