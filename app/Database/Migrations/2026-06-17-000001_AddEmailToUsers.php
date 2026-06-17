<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmailToUsers extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        if (!$db->fieldExists('email', 'users')) {
            $this->forge->addColumn('users', [
                'email' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                    'after'      => 'password_hash',
                ]
            ]);
            
            // Add unique index on email
            $db->query("ALTER TABLE users ADD UNIQUE KEY idx_email (email)");
        }
    }

    public function down()
    {
        // Don't drop email on users to avoid data loss during rollbacks if it was already there
    }
}
