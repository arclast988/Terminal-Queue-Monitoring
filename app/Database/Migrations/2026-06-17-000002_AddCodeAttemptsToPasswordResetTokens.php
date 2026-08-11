<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCodeAttemptsToPasswordResetTokens extends Migration
{
    public function up()
    {
        $this->forge->addColumn('password_reset_tokens', [
            'code_attempts' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
                'after'      => 'verified',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('password_reset_tokens', ['code_attempts']);
    }
}
