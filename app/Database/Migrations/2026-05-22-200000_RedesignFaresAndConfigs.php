<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Migration;

class RedesignFaresAndConfigs extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();
        $defaultTerminalId = $this->getDefaultTerminalId($db);

        $this->addTerminalIdColumn($db, 'announcements', 'fk_announcements_terminal', $defaultTerminalId);
        $this->addTerminalIdColumn($db, 'departure_rules', 'fk_departure_rules_terminal', $defaultTerminalId);
        $this->addTerminalIdColumn($db, 'fare_discounts', 'fk_fare_discounts_terminal', $defaultTerminalId);

        if ($db->tableExists('fare_discounts')) {
            $this->dropIndexIfExists($db, 'fare_discounts', 'fare_discounts_type_unique');
            $this->addUniqueIndexIfMissing($db, 'fare_discounts', 'fare_discounts_terminal_type_unique', ['terminal_id', 'type']);
        }

        foreach ($this->getTerminalIds($db, $defaultTerminalId) as $terminalId) {
            $this->ensureRegularDiscount($db, $terminalId);
        }

        $this->ensureFaresTable($db);

        if ($db->tableExists('routes') && $db->tableExists('fares') && $db->fieldExists('fare', 'routes')) {
            $routes = $db->table('routes')->get()->getResultArray();

            foreach ($routes as $route) {
                $terminalId = (int) ($route['terminal_id'] ?? $defaultTerminalId);
                $baseFare = (float) $route['fare'];
                $this->syncRouteFares($db, (int) $route['id'], $terminalId, $baseFare);
            }

            $db->query("ALTER TABLE `routes` DROP COLUMN `fare`");
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        if ($db->tableExists('routes') && !$db->fieldExists('fare', 'routes')) {
            $db->query("ALTER TABLE `routes` ADD COLUMN `fare` DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER `destination`");
        }

        if ($db->tableExists('routes') && $db->tableExists('fares') && $db->tableExists('fare_discounts')) {
            $db->query("
                UPDATE routes
                JOIN fares ON fares.route_id = routes.id
                JOIN fare_discounts ON fare_discounts.id = fares.fare_discount_id
                SET routes.fare = fares.amount
                WHERE fare_discounts.type = 'regular'
            ");
        }

        if ($db->tableExists('fares')) {
            $db->query("DROP TABLE `fares`");
        }

        if ($db->tableExists('fare_discounts')) {
            $db->table('fare_discounts')->where('type', 'regular')->delete();
            $this->dropIndexIfExists($db, 'fare_discounts', 'fare_discounts_terminal_type_unique');
            $this->addUniqueIndexIfMissing($db, 'fare_discounts', 'fare_discounts_type_unique', ['type']);
        }

        $this->dropTerminalIdColumn($db, 'announcements', 'fk_announcements_terminal');
        $this->dropTerminalIdColumn($db, 'departure_rules', 'fk_departure_rules_terminal');
        $this->dropTerminalIdColumn($db, 'fare_discounts', 'fk_fare_discounts_terminal');
    }

    private function addTerminalIdColumn(BaseConnection $db, string $table, string $constraint, int $defaultTerminalId): void
    {
        if (!$db->tableExists($table)) {
            return;
        }

        if (!$db->fieldExists('terminal_id', $table)) {
            $db->query("ALTER TABLE `{$table}` ADD COLUMN `terminal_id` INT UNSIGNED NOT NULL DEFAULT {$defaultTerminalId} AFTER `id`");
        }

        try {
            $db->query("ALTER TABLE `{$table}` ADD CONSTRAINT `{$constraint}` FOREIGN KEY (`terminal_id`) REFERENCES `terminals`(`id`) ON DELETE CASCADE");
        } catch (\Throwable $e) {
            // The foreign key may already exist on partially migrated databases.
        }
    }

    private function dropTerminalIdColumn(BaseConnection $db, string $table, string $constraint): void
    {
        if (!$db->tableExists($table) || !$db->fieldExists('terminal_id', $table)) {
            return;
        }

        try {
            $db->query("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraint}`");
        } catch (\Throwable $e) {
            // Continue; some environments use generated foreign key names.
        }

        $db->query("ALTER TABLE `{$table}` DROP COLUMN `terminal_id`");
    }

    private function ensureFaresTable(BaseConnection $db): void
    {
        if (!$db->tableExists('fares')) {
            $db->query("CREATE TABLE `fares` (
                `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                `route_id` INT UNSIGNED NOT NULL,
                `fare_discount_id` INT UNSIGNED NOT NULL,
                `amount` DECIMAL(10,2) NOT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT `fk_fares_route` FOREIGN KEY (`route_id`) REFERENCES `routes`(`id`) ON DELETE CASCADE,
                CONSTRAINT `fk_fares_discount` FOREIGN KEY (`fare_discount_id`) REFERENCES `fare_discounts`(`id`) ON DELETE CASCADE,
                UNIQUE KEY `unique_route_discount` (`route_id`, `fare_discount_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            return;
        }

        if (!$db->fieldExists('fare_discount_id', 'fares')) {
            $db->query("ALTER TABLE `fares` ADD COLUMN `fare_discount_id` INT UNSIGNED NULL AFTER `route_id`");

            $rows = $db->table('fares')
                ->select('fares.id, fares.route_id, routes.terminal_id')
                ->join('routes', 'routes.id = fares.route_id')
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                $regularId = $this->ensureRegularDiscount($db, (int) $row['terminal_id']);
                $db->table('fares')->where('id', $row['id'])->update(['fare_discount_id' => $regularId]);
            }

            $db->query("ALTER TABLE `fares` MODIFY `fare_discount_id` INT UNSIGNED NOT NULL");
        }

        $this->addUniqueIndexIfMissing($db, 'fares', 'unique_route_discount', ['route_id', 'fare_discount_id']);
        $this->dropIndexIfExists($db, 'fares', 'route_id');

        try {
            $db->query("ALTER TABLE `fares` ADD CONSTRAINT `fk_fares_discount` FOREIGN KEY (`fare_discount_id`) REFERENCES `fare_discounts`(`id`) ON DELETE CASCADE");
        } catch (\Throwable $e) {
            // The constraint may already exist.
        }
    }

    private function syncRouteFares(BaseConnection $db, int $routeId, int $terminalId, float $baseFare): void
    {
        $regularId = $this->ensureRegularDiscount($db, $terminalId);
        $discounts = $db->table('fare_discounts')
            ->where('terminal_id', $terminalId)
            ->get()
            ->getResultArray();

        if (!$discounts) {
            $discounts = [[
                'id' => $regularId,
                'type' => 'regular',
                'discount_percent' => 0,
            ]];
        }

        foreach ($discounts as $discount) {
            $amount = ($discount['type'] === 'regular')
                ? $baseFare
                : round($baseFare * (1 - ((float) $discount['discount_percent'] / 100)), 2);

            $this->upsertFare($db, $routeId, (int) $discount['id'], $amount);
        }
    }

    private function upsertFare(BaseConnection $db, int $routeId, int $discountId, float $amount): void
    {
        $existing = $db->table('fares')
            ->where('route_id', $routeId)
            ->where('fare_discount_id', $discountId)
            ->get()
            ->getRowArray();

        if ($existing) {
            $db->table('fares')->where('id', $existing['id'])->update(['amount' => $amount]);
            return;
        }

        $db->table('fares')->insert([
            'route_id' => $routeId,
            'fare_discount_id' => $discountId,
            'amount' => $amount,
        ]);
    }

    private function ensureRegularDiscount(BaseConnection $db, int $terminalId): int
    {
        $regular = $db->table('fare_discounts')
            ->where('terminal_id', $terminalId)
            ->where('type', 'regular')
            ->get()
            ->getRowArray();

        if ($regular) {
            return (int) $regular['id'];
        }

        $db->table('fare_discounts')->insert([
            'terminal_id' => $terminalId,
            'type' => 'regular',
            'label' => 'Regular Fare',
            'discount_percent' => 0.00,
            'is_active' => 1,
        ]);

        return (int) $db->insertID();
    }

    private function getDefaultTerminalId(BaseConnection $db): int
    {
        if (!$db->tableExists('terminals')) {
            return 1;
        }

        $terminal = $db->table('terminals')->select('id')->orderBy('id', 'ASC')->get(1)->getRowArray();
        return (int) ($terminal['id'] ?? 1);
    }

    private function getTerminalIds(BaseConnection $db, int $fallback): array
    {
        if (!$db->tableExists('terminals')) {
            return [$fallback];
        }

        $ids = array_map('intval', array_column($db->table('terminals')->select('id')->get()->getResultArray(), 'id'));
        return $ids ?: [$fallback];
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

        try {
            $db->query("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");
        } catch (\Throwable $e) {
            // Keep going when the index is required by an existing constraint.
        }
    }

    private function indexExists(BaseConnection $db, string $table, string $indexName): bool
    {
        $row = $db->query(
            "SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1",
            [$table, $indexName]
        )->getRowArray();

        return (bool) $row;
    }
}
