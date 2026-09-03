<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserIdToPasswordResetTokens extends Migration
{
    public function up()
    {
        $this->forge->addColumn('password_reset_tokens', [
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'id',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('password_reset_tokens', ['user_id']);
    }
}
