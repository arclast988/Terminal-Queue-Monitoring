<?php

namespace App\Controllers\Staff;

use App\Controllers\BaseController;
use App\Models\QueueModel;
use App\Models\TerminalModel;
use App\Models\AnnouncementModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $queueModel = new QueueModel();
        $terminalModel = new TerminalModel();

        $announcements = [];
        try {
            $announcementModel = new AnnouncementModel();
            $announcements = $announcementModel->where('is_active', 1)->orderBy('sort_order', 'ASC')->findAll();
        } catch (\Throwable $e) {
            // Table may not exist
        }

        $data = [
            'title' => 'Dispatcher Dashboard',
            'announcements' => $announcements,
            'active_queue_count' => $queueModel->whereIn('status', ['waiting', 'boarding'])->countAllResults(),
            'recent_departures' => $queueModel->select('queue.*, vehicles.plate_number, vehicles.type as vehicle_type')
                                              ->join('vehicles', 'vehicles.id = queue.vehicle_id')
                                              ->where('queue.status', 'departed')
                                              ->orderBy('departure_time', 'DESC')
                                              ->limit(5)
                                              ->findAll(),
            'terminals' => $terminalModel->findAll()
        ];

        return view('staff/dashboard', $data);
    }
}
