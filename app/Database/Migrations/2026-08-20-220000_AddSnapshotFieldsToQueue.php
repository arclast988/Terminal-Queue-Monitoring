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
            ],
            'operator_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'plate_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
        ]);

        // Backfill existing queue records with snapshot of current vehicle details
        $this->db->query("
            UPDATE queue
            SET
                driver_name = v.driver_name,
                operator_name = COALESCE(NULLIF(v.operator_name, ''), v.owner_name),
                plate_number = v.plate_number
            FROM vehicles v
            WHERE v.id = queue.vehicle_id
              AND (queue.driver_name IS NULL OR queue.driver_name = '')
        ");
    }

    public function down()
    {
        $this->forge->dropColumn('queue', ['driver_name', 'operator_name', 'plate_number']);
    }
}
