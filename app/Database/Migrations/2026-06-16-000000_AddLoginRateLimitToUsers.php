<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLoginRateLimitToUsers extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        $db->query("ALTER TABLE `users`
            ADD COLUMN `login_attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0
                AFTER `full_name`,
            ADD COLUMN `locked_until` DATETIME NULL DEFAULT NULL
                AFTER `login_attempts`");
    }

    public function down()
    {
        $db = \Config\Database::connect();

        $db->query("ALTER TABLE `users`
            DROP COLUMN `locked_until`,
            DROP COLUMN `login_attempts`");
    }
}
