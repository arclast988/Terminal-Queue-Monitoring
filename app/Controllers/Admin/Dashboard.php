<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VehicleModel;
use App\Models\RouteModel;
use App\Models\UserModel;
use App\Models\LogModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $vehicleModel = new VehicleModel();
        $routeModel = new RouteModel();
        $userModel = new UserModel();
        $logModel = new LogModel();

        $data = [
            'title' => 'Admin Dashboard',
            'stats' => [
                'vehicles' => $vehicleModel->countAll(),
                'routes'   => $routeModel->countAll(),
                'users'    => $userModel->countAll(),
                'logs'     => $logModel->countAll()
            ],
            'recent_logs' => $logModel->select('logs.*, users.username')
                                     ->join('users', 'users.id = logs.user_id', 'left')
                                     ->orderBy('timestamp', 'DESC')
                                     ->limit(5)
                                     ->findAll(),
            'destinations' => $routeModel->select('destination, vehicle_type')->distinct()->orderBy('destination', 'ASC')->findAll(),
            'vehicleTypes' => array_column($vehicleModel->select('type')->distinct()->orderBy('type', 'ASC')->findAll(), 'type')
        ];

        return view('admin/dashboard', $data);
    }
}

