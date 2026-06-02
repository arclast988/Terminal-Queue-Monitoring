<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates the user_routes linking table for dispatcher route assignments.
 * Only staff (dispatcher) users need entries here; admin users have access to all routes.
 */
class CreateUserRoutesTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if ($db->tableExists('user_routes')) {
            return;
        }

        $sql = "CREATE TABLE `user_routes` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `user_id` INT UNSIGNED NOT NULL,
            `route_id` INT UNSIGNED NOT NULL,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`route_id`) REFERENCES `routes`(`id`) ON DELETE CASCADE,
            UNIQUE KEY `unique_user_route` (`user_id`, `route_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

        $db->query($sql);
    }

    public function down()
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('user_routes')) {
            $this->forge->dropTable('user_routes');
        }
    }
}
