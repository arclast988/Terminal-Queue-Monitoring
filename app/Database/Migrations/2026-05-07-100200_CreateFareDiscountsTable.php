<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Creates the fare_discounts table used by FareDiscountModel and
 * referenced from the Fares controller and admin route management.
 *
 * Background: the table was originally created directly in the live
 * database (see jeepneynvans.sql) but no migration ever existed for
 * it. A fresh `php spark migrate` would leave the Fares page broken
 * without this file.
 *
 * Idempotent: uses CREATE TABLE IF NOT EXISTS, so it is safe to run
 * against the live DB where the table is already populated.
 */
class CreateFareDiscountsTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if ($db->tableExists('fare_discounts')) {
            return;
        }

        $sql = "CREATE TABLE IF NOT EXISTS `fare_discounts` (
            `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `type` VARCHAR(50) NOT NULL,
            `label` VARCHAR(100) NOT NULL,
            `discount_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
            `is_active` TINYINT(1) NOT NULL DEFAULT 1,
            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
            `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `fare_discounts_type_unique` (`type`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $db->query($sql);

        // Seed the default discount types used by the public fare display.
        $count = $db->table('fare_discounts')->countAllResults();
        if ($count === 0) {
            $db->table('fare_discounts')->insertBatch([
                ['type' => 'pwd',            'label' => 'PWD Discount',            'discount_percent' => 20.00, 'is_active' => 1],
                ['type' => 'senior_citizen', 'label' => 'Senior Citizen Discount', 'discount_percent' => 20.00, 'is_active' => 1],
                ['type' => 'student',        'label' => 'Student Discount',        'discount_percent' => 15.00, 'is_active' => 1],
            ]);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();
        if ($db->tableExists('fare_discounts')) {
            $this->forge->dropTable('fare_discounts');
        }
    }
}
