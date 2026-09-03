<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCurrentPassengersToQueue extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('current_passengers', 'queue')) {
            $fields = [
                'current_passengers' => [
                    'type'       => 'INT',
                    'constraint' => 5,
                    'default'    => 0,
                    'after'      => 'status'
                ],
            ];
            $this->forge->addColumn('queue', $fields);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('current_passengers', 'queue')) {
            $this->forge->dropColumn('queue', 'current_passengers');
        }
    }
}
