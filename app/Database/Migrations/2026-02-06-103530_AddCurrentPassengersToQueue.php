<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCurrentPassengersToQueue extends Migration
{
    public function up()
    {
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

    public function down()
    {
        $this->forge->dropColumn('queue', 'current_passengers');
    }
}
