<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class RemoveOperatorSimplifyVehicles extends Migration
{
    public function up()
    {
        // 1. Add driver_name to vehicles table
        $this->forge->addColumn('vehicles', [
            'driver_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'plate_number',
            ],
        ]);

        // 2. Copy owner_name data into driver_name for existing records
        $this->db->query("UPDATE vehicles SET driver_name = owner_name WHERE owner_name IS NOT NULL AND owner_name != ''");

        // 3. Drop vehicle_assignments table
        $this->forge->dropTable('vehicle_assignments', true);

        // 4. Reassign any operator users to staff
        $this->db->query("UPDATE users SET role = 'staff' WHERE role = 'operator'");
    }

    public function down()
    {
        // Remove driver_name column
        $this->forge->dropColumn('vehicles', 'driver_name');

        // Recreate vehicle_assignments table
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'vehicle_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'operator_user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'assigned_date' => [
                'type' => 'DATE',
            ],
            'shift_start' => [
                'type' => 'TIME',
            ],
            'shift_end' => [
                'type' => 'TIME',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('vehicle_assignments');
    }
}
