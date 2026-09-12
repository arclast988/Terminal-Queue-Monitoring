<?php

namespace App\Controllers;

use App\Models\QueueModel;
use App\Models\AnnouncementModel;

class History extends BaseController
{
    public function index()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to(base_url('login'))->with('error', 'Please log in to view departure history.');
        }

        $queueModel = new QueueModel();
        $queueModel->purgeOldDepartures(60);

        // Get filter parameters
        $search = $this->request->getGet('q');
        $destination = $this->request->getGet('destination');

        // Get assigned route IDs for staff dispatchers (admins see all)
        $assignedRouteIds = null;
        if (session()->get('role') === 'staff') {
            $userRouteModel = new \App\Models\UserRouteModel();
            $assignedRouteIds = $userRouteModel->getRouteIdsForUser((int) session()->get('id'));
        }

        // Fetch distinct destinations for filter pills
        $routeModel = new \App\Models\RouteModel();
        if ($assignedRouteIds !== null) {
            $destQuery = $routeModel->select('destination')->distinct();
            if (!empty($assignedRouteIds)) {
                $destQuery->whereIn('id', $assignedRouteIds);
            } else {
                $destQuery->where('id', 0);
            }
            $destinations = $destQuery->orderBy('destination', 'ASC')->findAll();
        } else {
            $destinations = $routeModel->select('destination')->distinct()->orderBy('destination', 'ASC')->findAll();
        }

        $builder = $queueModel->select('
            queue.*, 
            COALESCE(NULLIF(queue.plate_number, \'\'), vehicles.plate_number) as plate_number, 
            COALESCE(NULLIF(queue.driver_name, \'\'), vehicles.driver_name) as driver_name, 
            COALESCE(NULLIF(queue.operator_name, \'\'), NULLIF(vehicles.operator_name, \'\'), vehicles.owner_name) as operator_name,
            vehicles.owner_name, 
            vehicles.type as vehicle_type, 
            routes.destination, 
            terminals.name as origin
        ')
        ->withFullJoins()
        ->where('queue.status', 'departed');

        // Scope records for staff
        if ($assignedRouteIds !== null) {
            if (!empty($assignedRouteIds)) {
                $builder->whereIn('queue.route_id', $assignedRouteIds);
            } else {
                $builder->where('queue.route_id', 0);
            }
        }

        if ($destination && $destination !== 'all') {
            $builder->where('routes.destination', $destination);
        }

        if ($search) {
            $builder->groupStart()
                    ->like('COALESCE(NULLIF(queue.plate_number, \'\'), vehicles.plate_number)', $search)
                    ->orLike('COALESCE(NULLIF(queue.driver_name, \'\'), vehicles.driver_name)', $search)
                    ->orLike('COALESCE(NULLIF(queue.operator_name, \'\'), NULLIF(vehicles.operator_name, \'\'), vehicles.owner_name)', $search)
                    ->orLike('routes.destination', $search)
                    ->orLike('terminals.name', $search)
                    ->groupEnd();
        }

        $departures = $builder->orderBy('queue.departure_time', 'DESC')->paginate(20);

        $data = [
            'title' => 'Departure History',
            'body_class' => '',
            'departures' => $departures,
            'pager' => $queueModel->pager,
            'search' => $search,
            'destination' => $destination,
            'destinations' => $destinations,
        ];

        return view('shared/history', $data);
    }
}
