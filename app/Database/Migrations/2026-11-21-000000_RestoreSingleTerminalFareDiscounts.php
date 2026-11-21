<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;
use Throwable;

/** Restore existing discount policies without changing rates or stored fare quotes. */
class RestoreSingleTerminalFareDiscounts extends Migration
{
    public function up()
    {
        foreach (['terminals' => ['id'], 'fare_discounts' => ['id', 'terminal_id', 'type']] as $table => $required) {
            if (array_diff($required, $this->columns($table))) return;
        }

        if (!$this->db->transBegin()) throw new RuntimeException('Could not start the fare-discount reference repair.');
        try {
            if ($this->db->DBDriver === 'Postgre') {
                $this->db->query("SET LOCAL lock_timeout = '10s'");
                $tables = array_map(fn(string $table): string => $this->db->escapeIdentifiers($this->db->prefixTable($table)),
                    ['terminals', 'fare_discounts']);
                $this->db->query('LOCK TABLE ' . implode(', ', $tables) . ' IN SHARE ROW EXCLUSIVE MODE');
            }

            $terminals = $this->db->table('terminals')->select('id')->get(2)->getResultArray();
            if (count($terminals) === 1) {
                $terminalId = (int) $terminals[0]['id'];
                $discounts = $this->db->table('fare_discounts')->orderBy('id')->get()->getResultArray();
                foreach ($discounts as $discount) {
                    if ((int) ($discount['terminal_id'] ?? 0) === $terminalId) continue;
                    $conflict = $this->db->table('fare_discounts')->where('terminal_id', $terminalId)
                        ->where('type', $discount['type'])->where('id !=', $discount['id'])->countAllResults();
                    if ($conflict > 0) {
                        log_message('warning', 'Fare discount #{id} kept its terminal reference because terminal #{terminal} already has discount type {type}.',
                            ['id' => (int) $discount['id'], 'terminal' => $terminalId, 'type' => $discount['type']]);
                        continue;
                    }
                    // Keep policy identity, rates, active flags and timestamps; fares retain their existing policy IDs.
                    if (!$this->db->table('fare_discounts')->where('id', $discount['id'])->update(['terminal_id' => $terminalId])) {
                        throw new RuntimeException('Could not restore a fare-discount terminal reference.');
                    }
                }
            }
            if (!$this->db->transStatus() || !$this->db->transCommit()) {
                throw new RuntimeException('Could not commit the fare-discount reference repair.');
            }
        } catch (Throwable $error) {
            $this->db->transRollback();
            $this->db->resetTransStatus();
            throw $error;
        }
    }

    private function columns(string $table): array
    {
        if ($this->db->DBDriver === 'Postgre') {
            // Inspect the relation on the search path, not another schema's same-named table.
            return array_column($this->db->query(
                'SELECT attname FROM pg_attribute WHERE attrelid = to_regclass(?)'
                . ' AND attnum > 0 AND NOT attisdropped',
                [$this->db->escapeIdentifiers($this->db->prefixTable($table))]
            )->getResultArray(), 'attname');
        }
        return $this->db->tableExists($table) ? $this->db->getFieldNames($table) : [];
    }

    public function down()
    {
        // Never restore orphan references or detach discount policies on application rollback.
    }
}
