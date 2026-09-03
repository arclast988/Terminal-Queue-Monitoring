<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSuperAdminRole extends Migration
{
    public function up()
    {
        // The role column is now VARCHAR, so no ALTER needed to allow new values.
        // Just update the default to 'staff' via Forge (idempotent).
        $this->forge->modifyColumn('users', [
            'role' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'default'    => 'staff',
            ],
        ]);
    }

    public function down()
    {
        $db = \Config\Database::connect();
        $db->query("UPDATE users SET role = 'admin' WHERE role = 'super_admin'");

        $this->forge->modifyColumn('users', [
            'role' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'default'    => 'staff',
            ],
        ]);
    }
}
