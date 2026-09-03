<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Cleans up the `users.role` column definition.
 *
 * Background: the earlier RemoveOperatorSimplifyVehicles migration
 * reassigned every operator user to staff (UPDATE only). It never
 * altered the column definition, so the column still allows
 * 'operator' and even DEFAULTS to it. New rows inserted without an
 * explicit role would get the deprecated value.
 *
 * This migration:
 *   1. Defensively reassigns any lingering operator rows to staff,
 *      so the ALTER cannot truncate data.
 *   2. Sets the default to 'staff'.
 *
 * Idempotent: the UPDATE is a no-op when no operator rows exist.
 */
class CleanupUsersRoleEnum extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        // 1. Safety: ensure no row still holds the deprecated role.
        $db->query("UPDATE users SET role = 'staff' WHERE role = 'operator'");

        // 2. Set the column default to 'staff'.
        $this->forge->modifyColumn('users', [
            'role' => [
                'type'    => 'VARCHAR',
                'constraint' => 20,
                'null'    => false,
                'default' => 'staff',
            ],
        ]);
    }

    public function down()
    {
        // Restore the previous (looser) default.
        $this->forge->modifyColumn('users', [
            'role' => [
                'type'    => 'VARCHAR',
                'constraint' => 20,
                'null'    => false,
                'default' => 'operator',
            ],
        ]);
    }
}
