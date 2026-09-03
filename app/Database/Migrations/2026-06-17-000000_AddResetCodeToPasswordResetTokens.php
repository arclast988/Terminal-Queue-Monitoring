<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddResetCodeToPasswordResetTokens extends Migration
{
    public function up()
    {
        // Add reset_code column for 6-digit OTP
        $this->forge->addColumn('password_reset_tokens', [
            'reset_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 6,
                'null'       => true,
                'after'      => 'token',
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'reset_code',
            ],
            'verified' => [
                'type'       => 'SMALLINT',
                'constraint' => 1,
                'default'    => 0,
                'after'      => 'used',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('password_reset_tokens', ['reset_code', 'email', 'verified']);
    }
}
