<?php

namespace App\Controllers;

use App\Models\QueueModel;
use App\Models\RouteModel;
use App\Models\DepartureRuleModel;
use App\Models\AnnouncementModel;
use App\Models\VehicleTypeModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Schedules extends BaseController
{
    private function activeVehicleTypes(): array
    {
        try {
            $types = (new VehicleTypeModel())
                ->where('is_active', 1)
                ->orderBy('name', 'ASC')
                ->findAll();

            foreach ($types as &$type) {
                $type['image'] = vehicle_type_image($type['slug']);
            }
            unset($type);

            return $types;
        } catch (DatabaseException $e) {
            // Table probably doesn't exist yet. Return empty list and allow
            // the page to render so the admin can run migrations.
            log_message('error', 'Unable to load vehicle types: {message}', ['message' => $e->getMessage()]);
            return [];
        }
    }

    public function index()
    {
        $queueModel = new QueueModel();
        $routeModel = new RouteModel();
        $departureRuleModel = new DepartureRuleModel();

        // Get filter parameters
        $vehicleType = $this->request->getGet('type');
        $destination = $this->request->getGet('destination');
        $search      = trim((string) $this->request->getGet('q'));
        $vehicleTypes = $this->activeVehicleTypes();
        $activeTypeSlugs = array_column($vehicleTypes, 'slug');

        if ($vehicleType && !in_array($vehicleType, $activeTypeSlugs, true)) {
            $vehicleType = null;
        }

        // Load all departure rules (sorted by time_from)
        $departureRules = $departureRuleModel->orderBy('time_from', 'ASC')->findAll();

        // Build query - Active waiting and boarding vehicles only
        $builder = $queueModel->select('
                queue.id as queue_id,
                queue.status,
                queue.current_passengers,
                queue.position,
                queue.arrival_time,
                queue.estimated_departure,
                vehicles.id as vehicle_id,
                vehicles.plate_number,
                vehicles.type as vehicle_type,
                vehicles.capacity,
                vehicles.operator_name,
                vehicles.driver_name,
                routes.destination,
                terminals.name as origin
            ')
            ->withFullJoins()
            ->whereIn('queue.status', ['waiting', 'boarding']);

        // Apply filters
        if ($vehicleType) {
            $builder->where('vehicles.type', $vehicleType);
        }

        if ($destination) {
            $builder->where('routes.destination', strtoupper($destination));
        }

        if ($search !== '') {
            $builder->groupStart()
                ->like('vehicles.plate_number', $search)
                ->orLike('vehicles.operator_name', $search)
                ->orLike('vehicles.driver_name', $search)
                ->orLike('routes.destination', $search)
                ->orLike('terminals.name', $search)
            ->groupEnd();
        }

        // Sort active vehicles: Alphabetical destination, boarding first, then queue position
        $schedules = $builder->orderBy('routes.destination', 'ASC')
                             ->orderBy("CASE WHEN queue.status = 'boarding' THEN 0 ELSE 1 END", 'ASC')
                             ->orderBy('queue.position', 'ASC')
                             ->orderBy('queue.estimated_departure', 'ASC')
                             ->findAll();

        // Calculate full status
        foreach ($schedules as &$s) {
            $s['is_full'] = ((int) $s['current_passengers'] >= (int) $s['capacity']);
        }
        unset($s);

        // Compute active counts per destination (for filter chips with counts)
        $activeCountsRaw = $queueModel->select('routes.destination, count(queue.id) as total')
            ->join('routes', 'routes.id = queue.route_id')
            ->whereIn('queue.status', ['waiting', 'boarding'])
            ->groupBy('routes.destination')
            ->orderBy('routes.destination', 'ASC')
            ->findAll();

        $activeDestCounts = [];
        $totalActiveCount = 0;
        foreach ($activeCountsRaw as $row) {
            $destName = $row['destination'];
            $cnt = (int) $row['total'];
            $activeDestCounts[$destName] = $cnt;
            $totalActiveCount += $cnt;
        }

        // Destination options from admin-managed routes (so admin can add/edit/delete and they appear here)
        $allDestinations = $routeModel->select('destination')
            ->orderBy('destination', 'ASC')
            ->findColumn('destination') ?: [];
        $allDestinations = array_values(array_unique($allDestinations));

        $announcements = [];
        try {
            $announcements = $this->getActiveAnnouncements();
        } catch (\Throwable $e) {}

        $data = [
            'title'              => 'Vehicle Schedules',
            'body_class'         => session()->get('isLoggedIn') ? '' : 'public-page',
            'schedules'          => $schedules,
            'vehicle_type'       => $vehicleType,
            'destination'        => $destination,
            'search'             => $search,
            'all_destinations'   => $allDestinations,
            'active_dest_counts' => $activeDestCounts,
            'total_active_count' => $totalActiveCount,
            'vehicleTypes'       => $vehicleTypes,
            'announcements'      => $announcements
        ];

        // Use shared view for logged-in users, public view for guests
        if (session()->get('isLoggedIn')) {
            return view('shared/schedules', $data);
        }
        return view('public/schedules', $data);
    }

    /**
     * JSON endpoint for real-time schedule updates.
     * Called by WebSocket onmessage or polling fallback.
     * Accepts ?type=, ?destination=, and ?q= filter params.
     */
    public function status()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        $vehicleType = $this->request->getGet('type');
        $destination = $this->request->getGet('destination');
        $search      = substr(trim((string) $this->request->getGet('q')), 0, 32);
        $vehicleTypes = $this->activeVehicleTypes();
        if ($vehicleType && !in_array($vehicleType, array_column($vehicleTypes, 'slug'), true)) {
            $vehicleType = null;
        }
        if ($destination !== null) {
            $destination = substr(trim((string) $destination), 0, 100);
            if ($destination === '') {
                $destination = null;
            }
        }

        // Cache per filter-combination for a couple of seconds so repeated
        // public polls reuse one DB query. The sync token stays live below,
        // and broadcastUpdate() clears these keys so changes appear instantly.
        // Free-text search is NOT cached to prevent unbounded cache-key growth.
        $useCache  = ($search === '');
        $cacheKey  = 'rt_sched_status_' . hash('sha256', ($vehicleType ?? '') . '|' . ($destination ?? ''));
        $payload   = $useCache ? cache($cacheKey) : null;
        if (! is_array($payload)) {
            $queueModel = new QueueModel();

            $builder = $queueModel->select('
                    queue.id as queue_id,
                    queue.status,
                    queue.current_passengers,
                    queue.position,
                    queue.estimated_departure,
                    vehicles.plate_number,
                    vehicles.type as vehicle_type,
                    vehicles.capacity,
                    vehicles.operator_name,
                    vehicles.driver_name,
                    routes.destination,
                    terminals.name as origin
                ')
                ->withFullJoins()
                ->whereIn('queue.status', ['waiting', 'boarding']);

            if ($vehicleType) {
                $builder->where('vehicles.type', $vehicleType);
            }
            if ($destination) {
                $builder->where('routes.destination', strtoupper($destination));
            }
            if ($search !== '') {
                $builder->groupStart()
                    ->like('vehicles.plate_number', $search)
                    ->orLike('vehicles.operator_name', $search)
                    ->orLike('vehicles.driver_name', $search)
                    ->orLike('routes.destination', $search)
                    ->orLike('terminals.name', $search)
                ->groupEnd();
            }

            $schedules = $builder->orderBy('routes.destination', 'ASC')
                                 ->orderBy("CASE WHEN queue.status = 'boarding' THEN 0 ELSE 1 END", 'ASC')
                                 ->orderBy('queue.position', 'ASC')
                                 ->orderBy('queue.estimated_departure', 'ASC')
                                 ->findAll();

            foreach ($schedules as &$s) {
                if (empty($s['estimated_departure'])) {
                    $s['estimated_departure'] = null;
                }
                $s['is_full'] = ((int) $s['current_passengers'] >= (int) $s['capacity']);
                $s['estimated_departure_formatted'] = !empty($s['estimated_departure'])
                    ? date('g:i A', strtotime($s['estimated_departure'])) : null;
            }
            unset($s);

            // Compute active counts per destination (for live filter chips with counts)
            $activeCountsRaw = $queueModel->select('routes.destination, count(queue.id) as total')
                ->join('routes', 'routes.id = queue.route_id')
                ->whereIn('queue.status', ['waiting', 'boarding'])
                ->groupBy('routes.destination')
                ->orderBy('routes.destination', 'ASC')
                ->findAll();

            $activeDestCounts = [];
            $totalActiveCount = 0;
            foreach ($activeCountsRaw as $row) {
                $destName = $row['destination'];
                $cnt = (int) $row['total'];
                $activeDestCounts[$destName] = $cnt;
                $totalActiveCount += $cnt;
            }

            // Destination filter options, so the guest dropdown can refresh live
            // when an admin adds/removes a route (rides this same 3s poll).
            $allDestinations = (new RouteModel())
                ->select('destination')->orderBy('destination', 'ASC')->findColumn('destination') ?: [];
            $allDestinations = array_values(array_unique($allDestinations));

            $payload = [
                'schedules'          => $schedules,
                'count'              => count($schedules),
                'destinations'       => $allDestinations,
                'active_dest_counts' => $activeDestCounts,
                'total_active_count' => $totalActiveCount,
                'vehicle_types'      => $vehicleTypes,
            ];

            if ($useCache) {
                cache()->save($cacheKey, $payload, 2);
            }
        }

        $payload['sync_token'] = @file_get_contents(WRITEPATH . 'sync_token.txt') ?: '0';

        return $this->response
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setHeader('Pragma', 'no-cache')
            ->setJSON($payload);
    }
}
