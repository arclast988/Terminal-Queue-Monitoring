<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Migration;

class HardenFareRedesignTerminalIndexes extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if (!$db->tableExists('fare_discounts') || !$db->fieldExists('terminal_id', 'fare_discounts')) {
            return;
        }

        $this->dropIndexIfExists($db, 'fare_discounts', 'fare_discounts_type_unique');
        $this->addUniqueIndexIfMissing($db, 'fare_discounts', 'fare_discounts_terminal_type_unique', ['terminal_id', 'type']);

        foreach ($this->getTerminalIds($db) as $terminalId) {
            $this->ensureRegularDiscount($db, $terminalId);
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        if (!$db->tableExists('fare_discounts')) {
            return;
        }

        $this->dropIndexIfExists($db, 'fare_discounts', 'fare_discounts_terminal_type_unique');
        $this->addUniqueIndexIfMissing($db, 'fare_discounts', 'fare_discounts_type_unique', ['type']);
    }

    private function ensureRegularDiscount(BaseConnection $db, int $terminalId): void
    {
        $regular = $db->table('fare_discounts')
            ->where('terminal_id', $terminalId)
            ->where('type', 'regular')
            ->get()
            ->getRowArray();

        if ($regular) {
            return;
        }

        $db->table('fare_discounts')->insert([
            'terminal_id' => $terminalId,
            'type' => 'regular',
            'label' => 'Regular Fare',
            'discount_percent' => 0.00,
            'is_active' => 1,
        ]);
    }

    private function getTerminalIds(BaseConnection $db): array
    {
        if (!$db->tableExists('terminals')) {
            return [1];
        }

        $ids = array_map('intval', array_column($db->table('terminals')->select('id')->get()->getResultArray(), 'id'));
        return $ids ?: [1];
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
            "SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1",
            [$table, $indexName]
        )->getRowArray();

        return (bool) $row;
    }
}
