<?php

namespace App\Controllers;

use App\Models\QueueModel;
use App\Models\AnnouncementModel;

class Search extends BaseController
{
    public function index()
    {
        $search = trim((string) $this->request->getGet('q'));

        if ($search === '') {
            return redirect()->to('/guest');
        }

        $queueModel = new QueueModel();

        // Search in active queue
        $activeResults = $queueModel->select('queue.*, queue.estimated_departure, queue.current_passengers, vehicles.plate_number, vehicles.driver_name, vehicles.operator_name, vehicles.owner_name, vehicles.capacity, vehicles.type as vehicle_type, vehicles.photo as vehicle_photo, routes.destination, terminals.name as origin')
                                    ->withFullJoins()
                                    ->whereIn('queue.status', ['waiting', 'boarding'])
                                    ->groupStart()
                                        ->like('vehicles.plate_number', $search, 'both', null, true)
                                        ->orLike('vehicles.operator_name', $search, 'both', null, true)
                                        ->orLike('vehicles.driver_name', $search, 'both', null, true)
                                        ->orLike('routes.destination', $search, 'both', null, true)
                                        ->orLike('terminals.name', $search, 'both', null, true)
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
