<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VehicleModel;
use App\Models\RouteModel;
use App\Models\UserModel;
use App\Models\LogModel;
use App\Models\UserRouteModel;
use App\Models\VehicleTypeModel;

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
        $unassignedStaff = $userModel->select('COUNT(DISTINCT users.id) as count')
                                     ->join('user_routes', 'user_routes.user_id = users.id', 'left')
                                     ->where('users.role', 'staff')
                                     ->where('user_routes.id IS NULL')
                                     ->first()['count'] ?? 0;
        $unassignedStaff = (int) $unassignedStaff;

        $data = [
            'title' => 'Admin Dashboard',
            'stats' => [
                'vehicles' => $vehicleModel->countAll(),
                'routes'   => $routeModel->select('COUNT(DISTINCT CONCAT(terminal_id, "-", destination)) as cnt')->first()['cnt'] ?? 0,
                'users'    => $userModel->countAll(),
                'logs'     => $logModel->countAll()
            ],
            'unassignedVehicles' => $unassignedVehicles,
            'unassignedStaff'    => $unassignedStaff,
            'recent_logs' => $logModel->select('audit_logs.*, users.username, users.full_name, users.role, users.email')
                                     ->join('users', 'users.id = audit_logs.user_id', 'left')
                                     ->orderBy('timestamp', 'DESC')
                                     ->limit(5)
                                     ->findAll(),
            'destinations' => $routeModel->select('destination, vehicle_type')->distinct()->orderBy('destination', 'ASC')->findAll(),
            'vehicleTypes' => (new VehicleTypeModel())->where('is_active', 1)->orderBy('name', 'ASC')->findAll()
        ];

        return view('admin/dashboard', $data);
    }
}
