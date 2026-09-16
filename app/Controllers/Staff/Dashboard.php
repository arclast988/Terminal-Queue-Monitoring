<?php

namespace App\Controllers\Staff;

use App\Controllers\BaseController;
use App\Models\QueueModel;
use App\Models\TerminalModel;
use App\Models\AnnouncementModel;
use App\Models\UserRouteModel;
use App\Models\VehicleTypeModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $queueModel = new QueueModel();
        $terminalModel = new TerminalModel();
        $vehicleTypeModel = new VehicleTypeModel();

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
        if ($assignedRouteIds !== null) {
            if (!empty($assignedRouteIds)) {
                $countBuilder->whereIn('route_id', $assignedRouteIds);
            } else {
                $countBuilder->where('route_id', 0); // No routes = 0 results
            }
        }
        $activeQueueCount = $countBuilder->countAllResults();

        // Build filtered recent departures
        $departBuilder = $queueModel->select('
            queue.*, 
            COALESCE(NULLIF(queue.plate_number, \'\'), vehicles.plate_number) as plate_number,
            COALESCE(NULLIF(queue.driver_name, \'\'), vehicles.driver_name) as driver_name,
            COALESCE(NULLIF(queue.operator_name, \'\'), NULLIF(vehicles.operator_name, \'\'), vehicles.owner_name) as operator_name,
            vehicles.owner_name, 
            vehicles.capacity, 
            vehicles.type as vehicle_type, 
            vehicles.photo as vehicle_photo, 
            routes.destination, 
            terminals.name as origin
        ')
                                    ->withFullJoins()
                                    ->where('queue.status', 'departed')
                                    ->where('queue.departure_time >=', date('Y-m-d 00:00:00'))
                                    ->where('queue.departure_time <=', date('Y-m-d 23:59:59'));
        if ($assignedRouteIds !== null) {
            if (!empty($assignedRouteIds)) {
                $departBuilder->whereIn('queue.route_id', $assignedRouteIds);
            } else {
                $departBuilder->where('queue.route_id', 0);
            }
        }
        $recentDepartures = $departBuilder->orderBy('departure_time', 'DESC')
                                          ->limit(30)
                                          ->findAll();

        $data = [
            'title' => 'Dispatcher Dashboard',
            'announcements' => $announcements,
            'active_queue_count' => $activeQueueCount,
            'recent_departures' => $recentDepartures,
            'terminals' => $terminalModel->findAll(),
            'vehicleTypes' => $vehicleTypeModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll()
        ];

        return view('staff/dashboard', $data);
    }
}
