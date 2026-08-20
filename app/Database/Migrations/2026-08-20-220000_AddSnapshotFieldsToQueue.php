<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSnapshotFieldsToQueue extends Migration
{
    public function up()
    {
        $this->forge->addColumn('queue', [
            'driver_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'vehicle_id',
            ],
            'operator_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'driver_name',
            ],
            'plate_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'operator_name',
            ],
        ]);

        // Backfill existing queue records with snapshot of current vehicle details
        $this->db->query("
            UPDATE queue q
            JOIN vehicles v ON v.id = q.vehicle_id
            SET 
                q.driver_name = v.driver_name,
                q.operator_name = COALESCE(NULLIF(v.operator_name, ''), v.owner_name),
                q.plate_number = v.plate_number
            WHERE q.driver_name IS NULL OR q.driver_name = ''
        ");
    }

    public function down()
    {
        $this->forge->dropColumn('queue', ['driver_name', 'operator_name', 'plate_number']);
    }
}
