<?php

namespace App\Controllers;

use App\Models\QueueModel;
use App\Models\AnnouncementModel;

class Search extends BaseController
{
    public function index()
    {
        $search = $this->request->getGet('q');

        if (!$search) {
            return redirect()->to('/guest');
        }

        $queueModel = new QueueModel();

        // Search in active queue
        // Include vehicles.owner_name so views expecting `owner_name` won't error
        $activeResults = $queueModel->select('queue.*, queue.estimated_departure, vehicles.plate_number, vehicles.driver_name, vehicles.owner_name, vehicles.type as vehicle_type, routes.destination, routes.origin')
                                    ->join('vehicles', 'vehicles.id = queue.vehicle_id')
                                    ->join('routes', 'routes.id = queue.route_id')
                                    ->whereIn('queue.status', ['waiting', 'boarding'])
                                    ->groupStart()
                                        ->like('vehicles.plate_number', $search)
                                        ->orLike('vehicles.driver_name', $search)
                                        ->orLike('routes.destination', $search)
                                    ->groupEnd()
                                    ->orderBy('queue.position', 'ASC')
                                    ->findAll();

        // Search in recent departures
        // Include vehicles.owner_name for departed results as well
        $queueModel2 = new QueueModel();
        $departedResults = $queueModel2->select('queue.*, queue.estimated_departure, vehicles.plate_number, vehicles.driver_name, vehicles.owner_name, vehicles.type as vehicle_type, routes.destination, routes.origin')
                                       ->join('vehicles', 'vehicles.id = queue.vehicle_id')
                                       ->join('routes', 'routes.id = queue.route_id')
                                       ->where('queue.status', 'departed')
                                       ->groupStart()
                                           ->like('vehicles.plate_number', $search)
                                           ->orLike('vehicles.driver_name', $search)
                                           ->orLike('routes.destination', $search)
                                       ->groupEnd()
                                       ->orderBy('departure_time', 'DESC')
                                       ->limit(20)
                                       ->findAll();

        $announcements = [];
        try {
            $announcementModel = new AnnouncementModel();
            $announcements = $announcementModel->where('is_active', 1)->orderBy('sort_order', 'ASC')->findAll();
        } catch (\Throwable $e) {}

        $data = [
            'title' => 'Search Results',
            'search' => $search,
            'active_results' => $activeResults,
            'departed_results' => $departedResults,
            'total_results' => count($activeResults) + count($departedResults),
            'announcements' => $announcements
        ];

        return view('public/search', $data);
    }
}
