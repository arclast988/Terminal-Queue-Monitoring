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
 * Idempotent: uses createTable with IF NOT EXISTS, so it is safe to
 * run against the live DB where the table is already populated.
 */
class CreateFareDiscountsTable extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if ($db->tableExists('fare_discounts')) {
            return;
        }

        $this->forge->addField([
            'id'               => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'type'             => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => false],
            'label'            => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'discount_percent' => ['type' => 'DECIMAL', 'constraint' => '5,2', 'default' => 0.00],
            'is_active'        => ['type' => 'SMALLINT', 'constraint' => 1, 'default' => 1],
            'created_at'       => ['type' => 'TIMESTAMP', 'null' => true],
            'updated_at'       => ['type' => 'TIMESTAMP', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('type', 'fare_discounts_type_unique');
        $this->forge->createTable('fare_discounts', true);

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
