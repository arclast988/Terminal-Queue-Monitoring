<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSuperAdminRole extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        if ($db->DBDriver === 'SQLite3') {
            $this->forge->modifyColumn('users', [
                'role' => [
                    'type'       => 'TEXT',
                    'null'       => false,
                    'default'    => 'staff'
                ]
            ]);
        } else {
            $db->query("ALTER TABLE `users`
                MODIFY `role` ENUM('super_admin','admin','staff') NOT NULL DEFAULT 'staff'");
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $db->query("UPDATE `users` SET `role` = 'admin' WHERE `role` = 'super_admin'");
        if ($db->DBDriver === 'SQLite3') {
            $this->forge->modifyColumn('users', [
                'role' => [
                    'type'       => 'TEXT',
                    'null'       => false,
                    'default'    => 'staff'
                ]
            ]);
        } else {
            $db->query("ALTER TABLE `users`
                MODIFY `role` ENUM('admin','staff') NOT NULL DEFAULT 'staff'");
        }
    }
}
