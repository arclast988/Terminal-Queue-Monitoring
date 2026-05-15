<?php

namespace App\Controllers;

use App\Models\QueueModel;

class History extends BaseController
{
    public function index()
    {
        $queueModel = new QueueModel();

        // Get filter parameters
        $search = $this->request->getGet('q');

        $builder = $queueModel->select('queue.*, vehicles.plate_number, vehicles.owner_name, routes.destination, routes.origin')
                              ->join('vehicles', 'vehicles.id = queue.vehicle_id')
                              ->join('routes', 'routes.id = queue.route_id')
                              ->where('queue.status', 'departed');

        if ($search) {
            $builder->groupStart()
                    ->like('vehicles.plate_number', $search)
                    ->orLike('vehicles.owner_name', $search)
                    ->orLike('routes.destination', $search)
                    ->orLike('routes.origin', $search)
                    ->groupEnd();
        }

        $departures = $builder->orderBy('queue.departure_time', 'DESC')->paginate(20);

        $data = [
            'title' => 'Departure History',
            'body_class' => 'public-page',
            'departures' => $departures,
            'pager' => $queueModel->pager,
            'search' => $search
        ];

        if (session()->get('isLoggedIn')) {
            return view('shared/history', $data);
        }
        return view('public/history', $data);
    }
}
