<?php

namespace App\Controllers;

use App\Models\QueueModel;
use App\Models\AnnouncementModel;

class History extends BaseController
{
    public function index()
    {
        $queueModel = new QueueModel();
        $queueModel->purgeOldDepartures(60);

        // Get filter parameters
        $search = $this->request->getGet('q');

        // Announcements for the guest header marquee
        $announcements = [];
        try {
            $announcements = $this->getActiveAnnouncements();
        } catch (\Throwable $e) {
        }

        $builder = $queueModel->select('
            queue.*, 
            COALESCE(NULLIF(queue.plate_number, ""), vehicles.plate_number) as plate_number, 
            COALESCE(NULLIF(queue.driver_name, ""), vehicles.driver_name) as driver_name, 
            COALESCE(NULLIF(queue.operator_name, ""), NULLIF(vehicles.operator_name, ""), vehicles.owner_name) as operator_name, 
            vehicles.owner_name, 
            vehicles.type as vehicle_type, 
            routes.destination, 
            terminals.name as origin
        ')
        ->withFullJoins()
        ->where('queue.status', 'departed');

        if ($search) {
            $builder->groupStart()
                    ->like('queue.plate_number', $search)
                    ->orLike('vehicles.plate_number', $search)
                    ->orLike('queue.driver_name', $search)
                    ->orLike('vehicles.driver_name', $search)
                    ->orLike('queue.operator_name', $search)
                    ->orLike('vehicles.operator_name', $search)
                    ->orLike('vehicles.owner_name', $search)
                    ->orLike('routes.destination', $search)
                    ->orLike('terminals.name', $search)
                    ->groupEnd();
        }

        $departures = $builder->orderBy('queue.departure_time', 'DESC')->paginate(20);

        $data = [
            'title' => 'Departure History',
            'body_class' => session()->get('isLoggedIn') ? '' : 'public-page',
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
