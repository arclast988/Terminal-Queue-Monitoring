<?php

namespace App\Database\Migrations;

use App\Models\RouteModel;
use App\Models\VehicleModel;
use CodeIgniter\Database\Migration;

/** Remove impossible routes from service without deleting fares or trip history. */
class ArchiveSameLocationRoutes extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('routes') || !$this->db->tableExists('terminals')) {
            return;
        }

        $this->db->transBegin();
        try {
            // Dispatch and route edits use this lock before changing a line.
            $driver = strtolower($this->db->DBDriver);
            if (str_contains($driver, 'postgre')) {
                $key = crc32('queue_recalc_0');
                $this->db->query('SELECT pg_advisory_xact_lock(?)', [$key > 2147483647 ? $key - 4294967296 : $key]);
            } elseif (str_contains($driver, 'mysql')) {
                $this->db->query('SELECT id FROM routes ORDER BY id FOR UPDATE');
            }

            $routes = $this->db->table('routes')
                ->select('routes.id, routes.destination, routes.status, terminals.name as origin')
                ->join('terminals', 'terminals.id = routes.terminal_id')
                ->orderBy('routes.id')->get()->getResultArray();
            $invalid = array_values(array_filter($routes, static fn(array $route): bool => RouteModel::sameLocation(
                (string) $route['origin'], (string) $route['destination']
            )));
            $routeIds = array_map('intval', array_column($invalid, 'id'));
            $archiveIds = array_map('intval', array_column(array_filter($invalid, static fn(array $route): bool => $route['status'] !== 'archived'), 'id'));
            $vehicleIds = [];
            $tripIds = [];

            if ($routeIds) {
                if ($this->db->tableExists('vehicles')) {
                    $vehicleIds = array_map('intval', array_column($this->db->table('vehicles')
                        ->select('id')->whereIn('default_route_id', $routeIds)->orderBy('id')->get()->getResultArray(), 'id'));
                }
                if ($this->db->tableExists('queue')) {
                    $tripIds = array_map('intval', array_column($this->db->table('queue')
                        ->select('id')->whereIn('route_id', $routeIds)->whereIn('status', ['waiting', 'boarding'])
                        ->orderBy('id')->get()->getResultArray(), 'id'));
                }
            }

            if (!$archiveIds && !$vehicleIds && !$tripIds) {
                $this->db->transCommit();
                return;
            }
            if ($archiveIds) {
                $this->db->table('routes')->whereIn('id', $archiveIds)->update(['status' => 'archived']);
            }
            if ($vehicleIds) {
                $values = ['default_route_id' => null];
                foreach (['dispatch_order' => 0, 'dispatch_rotation' => 0, 'dispatch_rotation_date' => null] as $field => $value) {
                    if ($this->db->fieldExists($field, 'vehicles')) $values[$field] = $value;
                }
                $this->db->table('vehicles')->whereIn('id', $vehicleIds)->update($values);
                (new VehicleModel($this->db))->normalizeDispatchOrders();
            }
            if ($tripIds) {
                $values = ['status' => 'canceled', 'position' => 0];
                foreach (['boarding_start', 'estimated_departure'] as $field) {
                    if ($this->db->fieldExists($field, 'queue')) $values[$field] = null;
                }
                $this->db->table('queue')->whereIn('id', $tripIds)->update($values);
            }
            if ($this->db->tableExists('audit_logs')) {
                $this->db->table('audit_logs')->insert([
                    'user_id' => null,
                    'action' => 'Archive invalid same-location routes',
                    'details' => 'Same-location route IDs: ' . implode(', ', $routeIds)
                        . '; archived route IDs: ' . implode(', ', $archiveIds)
                        . '; unassigned vehicle IDs: ' . implode(', ', $vehicleIds)
                        . '; canceled active trip IDs: ' . implode(', ', $tripIds)
                        . '. Route records, fares, and completed trip history were preserved.',
                    'timestamp' => date('Y-m-d H:i:s'),
                ]);
            }
            if (!$this->db->transStatus()) {
                throw new \RuntimeException('Could not archive invalid same-location routes.');
            }
            $this->db->transCommit();
        } catch (\Throwable $error) {
            $this->db->transRollback();
            throw $error;
        }
    }

    public function down()
    {
        // Never reactivate invalid routes or re-open canceled trips on rollback.
    }
}
