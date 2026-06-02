<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VehicleModel;
use App\Models\RouteModel;
use App\Models\UserModel;
use App\Models\LogModel;
use App\Models\UserRouteModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $vehicleModel = new VehicleModel();
        $routeModel = new RouteModel();
        $userModel = new UserModel();
        $logModel = new LogModel();
        $userRouteModel = new UserRouteModel();

        // Count vehicles with no assigned route
        $unassignedVehicles = $vehicleModel->where('route_id IS NULL')->countAllResults();

        // Count staff users with no route assignments
        $staffUsers = $userModel->where('role', 'staff')->findAll();
        $unassignedStaff = 0;
        foreach ($staffUsers as $su) {
            $routeIds = $userRouteModel->getRouteIdsForUser((int) $su['id']);
            if (empty($routeIds)) {
                $unassignedStaff++;
            }
        }

        $data = [
            'title' => 'Admin Dashboard',
            'stats' => [
                'vehicles' => $vehicleModel->countAll(),
                'routes'   => $routeModel->countAll(),
                'users'    => $userModel->countAll(),
                'logs'     => $logModel->countAll()
            ],
            'unassignedVehicles' => $unassignedVehicles,
            'unassignedStaff'    => $unassignedStaff,
            'recent_logs' => $logModel->select('audit_logs.*, users.username')
                                     ->join('users', 'users.id = audit_logs.user_id', 'left')
                                     ->orderBy('timestamp', 'DESC')
                                     ->limit(5)
                                     ->findAll(),
            'destinations' => $routeModel->select('destination, vehicle_type')->distinct()->orderBy('destination', 'ASC')->findAll(),
            'vehicleTypes' => array_column($vehicleModel->select('type')->distinct()->orderBy('type', 'ASC')->findAll(), 'type')
        ];

        return view('admin/dashboard', $data);
    }
}


