<?php

namespace App\Controllers\Staff;

use App\Controllers\BaseController;
use App\Models\QueueModel;
use App\Models\TerminalModel;
use App\Models\AnnouncementModel;
use App\Models\UserRouteModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $queueModel = new QueueModel();
        $terminalModel = new TerminalModel();

        $announcements = [];
        try {
            $announcements = $this->getActiveAnnouncements();
        } catch (\Throwable $e) {
            // Table may not exist
        }

        // Get assigned routes for this dispatcher
        $assignedRouteIds = null;
        if (session()->get('role') === 'staff') {
            $userRouteModel = new UserRouteModel();
            $assignedRouteIds = $userRouteModel->getRouteIdsForUser((int) session()->get('id'));
        }

        // Build filtered queue count
        $countBuilder = $queueModel->whereIn('status', ['waiting', 'boarding']);
        if ($assignedRouteIds !== null && !empty($assignedRouteIds)) {
            $countBuilder->whereIn('route_id', $assignedRouteIds);
        } elseif ($assignedRouteIds !== null && empty($assignedRouteIds)) {
            $countBuilder->where('route_id', 0); // No routes = 0 results
        }
        $activeQueueCount = $countBuilder->countAllResults();

        // Build filtered recent departures
        $departBuilder = $queueModel->select('queue.*, vehicles.plate_number, vehicles.type as vehicle_type')
                                    ->withFullJoins()
                                    ->where('queue.status', 'departed')
                                    ->where('DATE(queue.departure_time)', date('Y-m-d'));
        if ($assignedRouteIds !== null && !empty($assignedRouteIds)) {
            $departBuilder->whereIn('queue.route_id', $assignedRouteIds);
        } elseif ($assignedRouteIds !== null && empty($assignedRouteIds)) {
            $departBuilder->where('queue.route_id', 0);
        }
        $recentDepartures = $departBuilder->orderBy('departure_time', 'DESC')
                                          ->limit(5)
                                          ->findAll();

        $data = [
            'title' => 'Dispatcher Dashboard',
            'announcements' => $announcements,
            'active_queue_count' => $activeQueueCount,
            'recent_departures' => $recentDepartures,
            'terminals' => $terminalModel->findAll()
        ];

        return view('staff/dashboard', $data);
    }
}
