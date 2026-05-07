<?php

namespace App\Controllers;

use App\Models\QueueModel;
use App\Models\AnnouncementModel;

class Home extends BaseController
{
    public function index()
    {
        $queueModel = new QueueModel();
        $routeModel = new \App\Models\RouteModel();

        $announcements = [];
        try {
            $announcementModel = new AnnouncementModel();
            $announcements = $announcementModel->where('is_active', 1)->orderBy('sort_order', 'ASC')->findAll();
        } catch (\Throwable $e) {
            // Table may not exist yet; show guest page without announcements
        }

        $active_queue = $queueModel->select('queue.*, queue.estimated_departure, vehicles.plate_number, vehicles.type as vehicle_type, routes.origin, routes.destination, queue.current_passengers, vehicles.capacity, vehicles.driver_name')
            ->join('vehicles', 'vehicles.id = queue.vehicle_id')
            ->join('routes', 'routes.id = queue.route_id')
            ->whereIn('queue.status', ['waiting', 'boarding'])
            ->orderBy('queue.position', 'ASC')
            ->findAll();


        $data = [
            'title' => 'Live Terminal Monitor',
            'active_queue' => $active_queue,
            'recent_departures' => $queueModel->select('queue.*, vehicles.plate_number, vehicles.capacity, queue.current_passengers, vehicles.driver_name, routes.origin, routes.destination')
                ->join('vehicles', 'vehicles.id = queue.vehicle_id')
                ->join('routes', 'routes.id = queue.route_id')
                ->where('queue.status', 'departed')
                ->orderBy('queue.departure_time', 'DESC')
                ->limit(10)
                ->findAll(),
            'total_departures_today' => $queueModel->where('status', 'departed')
                ->where('DATE(departure_time)', date('Y-m-d'))
                ->countAllResults(),
            'routes' => $routeModel->orderBy('destination', 'ASC')->findAll(),
            'announcements' => $announcements
        ];

        return view('public/enhanced_dashboard', $data);
    }

    public function status()
    {
        $queueModel = new QueueModel();

        $active_queue = $queueModel->select('queue.*, queue.estimated_departure, vehicles.plate_number, vehicles.type as vehicle_type, routes.origin, routes.destination, queue.current_passengers, vehicles.capacity, vehicles.driver_name')
            ->join('vehicles', 'vehicles.id = queue.vehicle_id')
            ->join('routes', 'routes.id = queue.route_id')
            ->whereIn('queue.status', ['waiting', 'boarding'])
            ->orderBy('queue.position', 'ASC')
            ->findAll();

        foreach ($active_queue as &$item) {
            $item['estimated_departure_formatted'] = !empty($item['estimated_departure']) ? date('h:i A', strtotime($item['estimated_departure'])) : 'N/A';
            $item['percent'] = min(100, ($item['current_passengers'] / max(1, $item['capacity'])) * 100);
        }

        $recent_departures = $queueModel->select('queue.*, vehicles.plate_number, routes.origin, routes.destination')
            ->join('vehicles', 'vehicles.id = queue.vehicle_id')
            ->join('routes', 'routes.id = queue.route_id')
            ->where('queue.status', 'departed')
            ->orderBy('queue.departure_time', 'DESC')
            ->limit(10)
            ->findAll();

        foreach ($recent_departures as &$dept) {
            $dept['departure_time_formatted'] = date('h:i A', strtotime($dept['departure_time']));
        }

        $total_departures_today = $queueModel->where('status', 'departed')
            ->where('DATE(departure_time)', date('Y-m-d'))
            ->countAllResults();

        $syncToken = @file_get_contents(WRITEPATH . 'sync_token.txt') ?: '0';

        $routeModel = new \App\Models\RouteModel();

        return $this->response
            ->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0')
            ->setJSON([
            'active_queue' => $active_queue,
            'recent_departures' => $recent_departures,
            'total_departures_today' => $total_departures_today,
            'sync_token' => $syncToken,
            'routes' => $routeModel->orderBy('destination', 'ASC')->findAll()
        ]);
    }
}
