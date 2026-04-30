<?php

namespace App\Controllers;

use App\Models\QueueModel;
use App\Models\RouteModel;
use App\Models\DepartureRuleModel;
use App\Models\AnnouncementModel;

class Schedules extends BaseController
{
    public function index()
    {

        $queueModel = new QueueModel();
        $routeModel = new RouteModel();
        $departureRuleModel = new DepartureRuleModel();

        // Get filter parameters
        $vehicleType = $this->request->getGet('type');
        $destination = $this->request->getGet('destination');

        // Load all departure rules (sorted by time_from)
        $departureRules = $departureRuleModel->orderBy('time_from', 'ASC')->findAll();

        // Build query - Based on the queue table for today's schedules
        $builder = $queueModel->select('
                queue.id as queue_id,
                queue.status,
                queue.current_passengers,
                queue.position,
                queue.arrival_time,
                queue.estimated_departure,
                queue.departure_time,
                vehicles.id as vehicle_id,
                vehicles.plate_number,
                vehicles.type as vehicle_type,
                vehicles.capacity,
                vehicles.driver_name,
                routes.destination,
                routes.origin
            ')
            ->join('vehicles', 'vehicles.id = queue.vehicle_id')
            ->join('routes', 'routes.id = queue.route_id')
            ->groupStart()
                ->whereIn('queue.status', ['waiting', 'boarding'])
                ->orGroupStart()
                    ->where('queue.status', 'departed')
                    ->where('DATE(queue.arrival_time)', date('Y-m-d'))
                ->groupEnd()
            ->groupEnd();

        // Apply filters
        if ($vehicleType && in_array($vehicleType, ['van', 'jeepney', 'minibus'])) {
            $builder->where('vehicles.type', $vehicleType);
        }

        if ($destination) {
            $builder->where('routes.destination', $destination);
        }

        // Sort active vehicles (waiting/boarding) first, then departed vehicles, then by their queue position
        $schedules = $builder->orderBy("CASE WHEN queue.status = 'departed' THEN 1 ELSE 0 END", 'ASC')
                             ->orderBy('queue.position', 'ASC')
                             ->findAll();

        // Calculate full status
        foreach ($schedules as &$s) {
            if (empty($s['estimated_departure'])) {
                // Fallback for legacy records that don't have an explicit estimated departure
                $s['estimated_departure'] = $s['arrival_time'];
            }
            $s['is_full'] = ((int) $s['current_passengers'] >= (int) $s['capacity']);
        }
        unset($s);

        // Destination options from admin-managed routes (so admin can add/edit/delete and they appear here)
        $vanDestinations = $routeModel->select('destination')
            ->where('vehicle_type', 'van')
            ->orderBy('destination', 'ASC')
            ->findColumn('destination') ?: [];
        $vanDestinations = array_values(array_unique($vanDestinations));

        $jeepneyDestinations = $routeModel->select('destination')
            ->where('vehicle_type', 'jeepney')
            ->orderBy('destination', 'ASC')
            ->findColumn('destination') ?: [];
        $jeepneyDestinations = array_values(array_unique($jeepneyDestinations));

        $minibusDestinations = $routeModel->select('destination')
            ->where('vehicle_type', 'minibus')
            ->orderBy('destination', 'ASC')
            ->findColumn('destination') ?: [];
        $minibusDestinations = array_values(array_unique($minibusDestinations));

        $announcements = [];
        try {
            $announcementModel = new AnnouncementModel();
            $announcements = $announcementModel->where('is_active', 1)->orderBy('sort_order', 'ASC')->findAll();
        } catch (\Throwable $e) {}

        $data = [
            'title' => 'Vehicle Schedules',
            'body_class' => 'public-page',
            'schedules' => $schedules,
            'vehicle_type' => $vehicleType,
            'destination' => $destination,
            'van_destinations' => $vanDestinations,
            'jeepney_destinations' => $jeepneyDestinations,
            'minibus_destinations' => $minibusDestinations,
            'announcements' => $announcements
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
     * Accepts same ?type= and ?destination= filter params.
     */
    public function status()
    {

        $queueModel = new QueueModel();

        $vehicleType = $this->request->getGet('type');
        $destination = $this->request->getGet('destination');

        $builder = $queueModel->select('
                queue.id as queue_id,
                queue.status,
                queue.current_passengers,
                queue.position,
                queue.estimated_departure,
                queue.departure_time,
                vehicles.plate_number,
                vehicles.type as vehicle_type,
                vehicles.capacity,
                routes.destination,
                routes.origin
            ')
            ->join('vehicles', 'vehicles.id = queue.vehicle_id')
            ->join('routes', 'routes.id = queue.route_id')
            ->groupStart()
                ->whereIn('queue.status', ['waiting', 'boarding'])
                ->orGroupStart()
                    ->where('queue.status', 'departed')
                    ->where('DATE(queue.arrival_time)', date('Y-m-d'))
                ->groupEnd()
            ->groupEnd();

        if ($vehicleType && in_array($vehicleType, ['van', 'jeepney', 'minibus'])) {
            $builder->where('vehicles.type', $vehicleType);
        }
        if ($destination) {
            $builder->where('routes.destination', $destination);
        }

        $schedules = $builder->orderBy("CASE WHEN queue.status = 'departed' THEN 1 ELSE 0 END", 'ASC')
                             ->orderBy('queue.position', 'ASC')
                             ->findAll();

        foreach ($schedules as &$s) {
            if (empty($s['estimated_departure'])) {
                $s['estimated_departure'] = null;
            }
            $s['is_full'] = ((int) $s['current_passengers'] >= (int) $s['capacity']);
            $s['estimated_departure_formatted'] = !empty($s['estimated_departure'])
                ? date('g:i A', strtotime($s['estimated_departure'])) : null;
            $s['departure_time_formatted'] = !empty($s['departure_time'])
                ? date('g:i A', strtotime($s['departure_time'])) : null;
        }
        unset($s);

        $syncToken = @file_get_contents(WRITEPATH . 'sync_token.txt') ?: '0';

        return $this->response
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setHeader('Pragma', 'no-cache')
            ->setJSON([
                'schedules' => $schedules,
                'count'     => count($schedules),
                'sync_token' => $syncToken
            ]);
    }
}