<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;
use Throwable;

/** Restore legacy references only when the terminal choice is unambiguous. */
class RestoreSingleTerminalDepartureRules extends Migration
{
    public function up()
    {
        $columns = [];
        foreach (['terminals', 'departure_rules', 'routes'] as $table) $columns[$table] = $this->columns($table);
        foreach (['terminals' => ['id'], 'departure_rules' => ['id', 'terminal_id']] as $table => $fields) {
            if (array_diff($fields, $columns[$table])) return;
        }

        $hasRouteId = in_array('route_id', $columns['departure_rules'], true);
        $hasRoutes = !array_diff(['id', 'terminal_id'], $columns['routes']);
        if (!$this->db->transBegin()) throw new RuntimeException('Could not start the departure-rule reference repair.');
        try {
            if ($this->db->DBDriver === 'Postgre') {
                $this->db->query("SET LOCAL lock_timeout = '10s'");
                $tables = ['terminals', 'departure_rules'];
                if ($hasRoutes) $tables[] = 'routes';
                $this->db->query('LOCK TABLE ' . implode(', ', array_map(
                    fn(string $table): string => $this->db->escapeIdentifiers($this->db->prefixTable($table)), $tables
                )) . ' IN SHARE ROW EXCLUSIVE MODE');
            }

            $terminals = $this->db->table('terminals')->select('id')->get(2)->getResultArray();
            if (count($terminals) === 1) {
                $terminalId = (int) $terminals[0]['id'];
                $indexes = $this->uniqueIndexes();
                $rules = $this->db->table('departure_rules')->orderBy('id')->get()->getResultArray();
                foreach ($rules as $rule) {
                    if ((int) ($rule['terminal_id'] ?? 0) === $terminalId) continue;
                    if ($hasRouteId && $rule['route_id'] !== null) {
                        $route = $hasRoutes ? $this->db->table('routes')->select('terminal_id')
                            ->where('id', $rule['route_id'])->get()->getRowArray() : null;
                        if (!$route || (int) $route['terminal_id'] !== $terminalId) {
                            $this->warn((int) $rule['id'], 'its destination does not belong to the sole existing terminal');
                            continue;
                        }
                    }
                    $conflict = $this->conflictingIndex(array_replace($rule, ['terminal_id' => $terminalId]), $indexes);
                    if ($conflict !== null) {
                        $this->warn((int) $rule['id'], 'unique index ' . $conflict . ' requires manual review');
                        continue;
                    }
                    // Preserve the schedule, route, round, timestamps and rule identity.
                    if (!$this->db->table('departure_rules')->where('id', $rule['id'])->update(['terminal_id' => $terminalId])) {
                        throw new RuntimeException('Could not restore a departure-rule terminal reference.');
                    }
                }
            }
            if (!$this->db->transStatus() || !$this->db->transCommit()) {
                throw new RuntimeException('Could not commit the departure-rule reference repair.');
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
            // The framework's fieldExists() combines same-named tables from every schema.
            return array_column($this->db->query(
                'SELECT attname FROM pg_attribute WHERE attrelid = to_regclass(?)'
                . ' AND attnum > 0 AND NOT attisdropped',
                [$this->db->escapeIdentifiers($this->db->prefixTable($table))]
            )->getResultArray(), 'attname');
        }
        return $this->db->tableExists($table) ? $this->db->getFieldNames($table) : [];
    }

    /** Read actual key columns; partial keys are checked conservatively against every row. */
    private function uniqueIndexes(): array
    {
        $indexes = [];
        $table = $this->db->prefixTable('departure_rules');
        if ($this->db->DBDriver === 'Postgre') {
            $rows = $this->db->query(
                "SELECT c.relname AS name, a.attname AS field, COALESCE((to_jsonb(i)->>'indnullsnotdistinct')::boolean, false) AS nulls_not_distinct FROM pg_index i"
                . ' JOIN pg_class c ON c.oid = i.indexrelid'
                . ' CROSS JOIN LATERAL unnest(i.indkey) WITH ORDINALITY AS k(attnum, ord)'
                . ' LEFT JOIN pg_attribute a ON a.attrelid = i.indrelid AND a.attnum = k.attnum'
                . ' WHERE i.indrelid = to_regclass(?) AND i.indisunique AND k.ord <= i.indnkeyatts'
                . ' ORDER BY c.relname, k.ord', [$this->db->escapeIdentifiers($table)]
            )->getResultArray();
            foreach ($rows as $row) {
                $indexes[$row['name']]['fields'][] = $row['field'];
                $indexes[$row['name']]['nulls_distinct'] = !in_array($row['nulls_not_distinct'], [true, 't', '1', 1], true);
            }
        } elseif ($this->db->DBDriver === 'SQLite3') {
            $rows = $this->db->query('PRAGMA index_list(' . $this->db->escape($table) . ')')->getResultArray();
            foreach ($rows as $row) {
                if (!(int) $row['unique']) continue;
                $indexes[$row['name']] = ['fields' => [], 'nulls_distinct' => true];
                $columns = $this->db->query('PRAGMA index_xinfo(' . $this->db->escape($row['name']) . ')')->getResultArray();
                foreach ($columns as $column) {
                    if ((int) $column['key']) $indexes[$row['name']]['fields'][] = $column['name'];
                }
            }
        } else {
            foreach ($this->db->getIndexData('departure_rules') as $index) {
                if (in_array($index->type, ['UNIQUE', 'PRIMARY'], true)) {
                    $indexes[$index->name] = ['fields' => $index->fields, 'nulls_distinct' => false];
                }
            }
        }
        return $indexes;
    }

    private function conflictingIndex(array $rule, array $indexes): ?string
    {
        foreach ($indexes as $name => $index) {
            $fields = $index['fields'];
            // An expression key cannot be safely predicted from its column values.
            if (in_array(null, $fields, true)) return $name;
            if (!in_array('terminal_id', $fields, true)) continue;
            if ($index['nulls_distinct'] && array_filter($fields,
                static fn(string $field): bool => array_key_exists($field, $rule) && $rule[$field] === null)) continue;
            $query = $this->db->table('departure_rules')->where('id !=', $rule['id']);
            foreach ($fields as $field) {
                if (!array_key_exists($field, $rule)) return $name;
                $query->where($field, $rule[$field]);
            }
            if ($query->countAllResults() > 0) return $name;
        }
        return null;
    }

    private function warn(int $ruleId, string $reason): void
    {
        log_message('warning', 'Departure rule #{id} kept its terminal reference because {reason}.', ['id' => $ruleId, 'reason' => $reason]);
    }

    public function down()
    {
        // Do not restore invalid references when rolling back the application.
    }
}
