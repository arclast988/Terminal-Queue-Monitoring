<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Migration;

class RemoveRouteOriginUseTerminal extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if (!$db->tableExists('routes')) {
            return;
        }

        $this->dropIndexIfExists($db, 'routes', 'unique_terminal_destination_vehicle');

        if ($db->fieldExists('origin', 'routes')) {
            $db->query('ALTER TABLE `routes` DROP COLUMN `origin`');
        }

        if ($db->fieldExists('terminal_id', 'routes')) {
            $db->query('ALTER TABLE `routes` MODIFY `terminal_id` INT(11) UNSIGNED NOT NULL AFTER `id`');
        }

        if ($db->fieldExists('destination', 'routes')) {
            $db->query('ALTER TABLE `routes` MODIFY `destination` VARCHAR(100) NOT NULL AFTER `terminal_id`');
        }

        if ($db->fieldExists('vehicle_type', 'routes')) {
            $db->query("ALTER TABLE `routes` MODIFY `vehicle_type` ENUM('jeepney','van','minibus') NOT NULL DEFAULT 'van' AFTER `destination`");
        }

        if ($db->fieldExists('created_at', 'routes')) {
            $db->query('ALTER TABLE `routes` MODIFY `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP AFTER `vehicle_type`');
        }

        $this->addUniqueIndexIfMissing($db, 'routes', 'unique_terminal_destination_vehicle', ['terminal_id', 'destination', 'vehicle_type']);
    }

    public function down()
    {
        $db = \Config\Database::connect();

        if (!$db->tableExists('routes')) {
            return;
        }

        $this->dropIndexIfExists($db, 'routes', 'unique_terminal_destination_vehicle');

        if (!$db->fieldExists('origin', 'routes')) {
            $db->query("ALTER TABLE `routes` ADD COLUMN `origin` VARCHAR(100) NOT NULL DEFAULT '' AFTER `id`");
        }

        if ($db->tableExists('terminals')) {
            $db->query("
                UPDATE routes
                JOIN terminals ON terminals.id = routes.terminal_id
                SET routes.origin = UPPER(TRIM(terminals.name))
            ");
        }

        if ($db->fieldExists('destination', 'routes')) {
            $db->query('ALTER TABLE `routes` MODIFY `destination` VARCHAR(100) NOT NULL AFTER `origin`');
        }

        if ($db->fieldExists('vehicle_type', 'routes')) {
            $db->query("ALTER TABLE `routes` MODIFY `vehicle_type` ENUM('jeepney','van','minibus') NOT NULL DEFAULT 'van' AFTER `destination`");
        }

        if ($db->fieldExists('terminal_id', 'routes')) {
            $db->query('ALTER TABLE `routes` MODIFY `terminal_id` INT(11) UNSIGNED NOT NULL AFTER `vehicle_type`');
        }

        if ($db->fieldExists('created_at', 'routes')) {
            $db->query('ALTER TABLE `routes` MODIFY `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP AFTER `terminal_id`');
        }
    }

    private function addUniqueIndexIfMissing(BaseConnection $db, string $table, string $indexName, array $columns): void
    {
        if ($this->indexExists($db, $table, $indexName)) {
            return;
        }

        $columnList = implode('`, `', $columns);
        $db->query("ALTER TABLE `{$table}` ADD UNIQUE KEY `{$indexName}` (`{$columnList}`)");
    }

    private function dropIndexIfExists(BaseConnection $db, string $table, string $indexName): void
    {
        if (!$this->indexExists($db, $table, $indexName)) {
            return;
        }

        $db->query("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");
    }

    private function indexExists(BaseConnection $db, string $table, string $indexName): bool
    {
        $row = $db->query(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1',
            [$table, $indexName]
        )->getRowArray();

        return (bool) $row;
    }
}
