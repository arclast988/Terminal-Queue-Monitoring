<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLoginRateLimitToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'login_attempts' => [
                'type'       => 'SMALLINT',
                'default'    => 0,
                'null'       => false,
            ],
            'locked_until' => [
                'type'       => 'TIMESTAMP',
                'null'       => true,
                'default'    => null,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['locked_until', 'login_attempts']);
    }
}
