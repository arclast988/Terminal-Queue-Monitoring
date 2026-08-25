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
        $activeResults = $queueModel->select('queue.*, queue.estimated_departure, queue.departure_time, queue.current_passengers, vehicles.plate_number, vehicles.driver_name, vehicles.operator_name, vehicles.owner_name, vehicles.capacity, vehicles.type as vehicle_type, routes.destination, terminals.name as origin')
                                    ->withFullJoins()
                                    ->whereIn('queue.status', ['waiting', 'boarding'])
                                    ->groupStart()
                                        ->like('vehicles.plate_number', $search)
                                        ->orLike('vehicles.operator_name', $search)
                                        ->orLike('vehicles.driver_name', $search)
                                        ->orLike('routes.destination', $search)
                                        ->orLike('terminals.name', $search)
                                    ->groupEnd()
                                    ->orderBy('queue.position', 'ASC')
                                    ->orderBy('routes.destination', 'ASC')
                                    ->findAll();

        $announcements = [];
        try {
            $announcements = $this->getActiveAnnouncements();
        } catch (\Throwable $e) {}

        $data = [
            'title' => 'Search Results',
            'search' => $search,
            'active_results' => $activeResults,
            'total_results' => count($activeResults),
            'announcements' => $announcements
        ];

        return view('public/search', $data);
    }
}
