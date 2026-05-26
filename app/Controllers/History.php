<?php

namespace App\Controllers;

use App\Models\QueueModel;
use App\Models\AnnouncementModel;

class History extends BaseController
{
    public function index()
    {
        $queueModel = new QueueModel();

        // Get filter parameters
        $search = $this->request->getGet('q');

        // Announcements for the guest header marquee
        $announcements = [];
        try {
            $announcementModel = new AnnouncementModel();
            $announcements = $announcementModel->where('is_active', 1)
                ->orderBy('sort_order', 'ASC')
                ->findAll();
        } catch (\Throwable $e) {
        }

        $builder = $queueModel->select('queue.*, vehicles.plate_number, vehicles.driver_name, vehicles.owner_name, vehicles.type as vehicle_type, routes.destination, terminals.name as origin')
                              ->join('vehicles', 'vehicles.id = queue.vehicle_id')
                              ->join('routes', 'routes.id = queue.route_id')
                              ->join('terminals', 'terminals.id = routes.terminal_id')
                              ->where('queue.status', 'departed');

        if ($search) {
            $builder->groupStart()
                    ->like('vehicles.plate_number', $search)
                    ->orLike('vehicles.driver_name', $search)
                    ->orLike('routes.destination', $search)
                    ->orLike('terminals.name', $search)
                    ->groupEnd();
        }

        $departures = $builder->orderBy('queue.departure_time', 'DESC')->paginate(20);

        $data = [
            'title' => 'Departure History',
            'body_class' => 'public-page',
            'departures' => $departures,
            'pager' => $queueModel->pager,
            'search' => $search,
            'announcements' => $announcements,
        ];

        if (session()->get('isLoggedIn')) {
            return view('shared/history', $data);
        }
        return view('public/history', $data);
    }
}
