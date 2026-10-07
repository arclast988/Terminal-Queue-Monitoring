<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVehicleDispatchOrder extends Migration
{
    public function up()
    {
        $this->forge->addColumn('vehicles', [
            'dispatch_order' => ['type' => 'INT', 'default' => 0],
            'dispatch_rotation' => ['type' => 'INT', 'default' => 0],
            'dispatch_rotation_date' => ['type' => 'DATE', 'null' => true],
        ]);

        // Keep the existing type/plate starting order when upgrading.
        $vehicles = $this->db->table('vehicles')->select('vehicles.id, vehicles.default_route_id, routes.terminal_id, routes.destination')
            ->join('routes', 'routes.id = vehicles.default_route_id')
            ->orderBy('vehicles.type', 'ASC')->orderBy('vehicles.plate_number', 'ASC')->orderBy('vehicles.id', 'ASC')
            ->get()->getResultArray();
        $positions = [];
        $groups = [];
        foreach ($vehicles as $vehicle) {
            $key = $vehicle['terminal_id'] . '|' . $vehicle['destination'];
            $positions[$key] = ($positions[$key] ?? 0) + 1;
            $groups[(int) $vehicle['id']] = $key;
            $this->db->table('vehicles')->where('id', $vehicle['id'])->update(['dispatch_order' => $positions[$key]]);
        }

        // Preserve today's rotation rather than restarting it during deployment.
        $departures = $this->db->table('queue')->select('queue.vehicle_id, routes.terminal_id, routes.destination')
            ->join('routes', 'routes.id = queue.route_id')->where('queue.status', 'departed')
            ->where('queue.departure_time >=', date('Y-m-d') . ' 00:00:00')
            ->where('queue.departure_time <', date('Y-m-d', strtotime('tomorrow')) . ' 00:00:00')
            ->orderBy('queue.departure_time', 'ASC')->orderBy('queue.id', 'ASC')->get()->getResultArray();
        $rotations = [];
        foreach ($departures as $departure) {
            $key = $departure['terminal_id'] . '|' . $departure['destination'];
            if (($groups[(int) $departure['vehicle_id']] ?? null) !== $key) continue;
            $rotations[$key] = ($rotations[$key] ?? 0) + 1;
            $this->db->table('vehicles')->where('id', $departure['vehicle_id'])->update([
                'dispatch_rotation' => $rotations[$key], 'dispatch_rotation_date' => date('Y-m-d'),
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('vehicles', ['dispatch_order', 'dispatch_rotation', 'dispatch_rotation_date']);
    }
}
