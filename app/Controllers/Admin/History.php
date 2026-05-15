<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\QueueModel;

class History extends BaseController
{
    public function index()
    {
        $search = $this->request->getGet('q');
        $fromDate = $this->request->getGet('from_date');
        $toDate = $this->request->getGet('to_date');
        $destFilter = $this->request->getGet('destination');
        $typeFilter = $this->request->getGet('vehicle_type');

        // --- Stats (fresh query for each to avoid filter stacking) ---
        $queueModel1 = new QueueModel();
        $totalAll = $queueModel1->where('queue.status', 'departed')
                                 ->where('queue.departure_time IS NOT NULL')
                                 ->countAllResults();

        $queueModel2 = new QueueModel();
        $totalToday = $queueModel2->where('queue.status', 'departed')
                                   ->where('queue.departure_time IS NOT NULL')
                                   ->where('DATE(queue.departure_time)', date('Y-m-d'))
                                   ->countAllResults();

        $queueModel3 = new QueueModel();
        $totalMonth = $queueModel3->where('queue.status', 'departed')
                                   ->where('queue.departure_time IS NOT NULL')
                                   ->where('YEAR(queue.departure_time)', date('Y'))
                                   ->where('MONTH(queue.departure_time)', date('m'))
                                   ->countAllResults();

        $queueModel4 = new QueueModel();
        $totalYear = $queueModel4->where('queue.status', 'departed')
                                  ->where('queue.departure_time IS NOT NULL')
                                  ->where('YEAR(queue.departure_time)', date('Y'))
                                  ->countAllResults();

        // --- Options for Filter Modal ---
        $routeModel = new \App\Models\RouteModel();
        $vehicleModel = new \App\Models\VehicleModel();
        
        $destinations = $routeModel->select('destination')->distinct()->orderBy('destination', 'ASC')->findAll();
        $vehicleTypes = $vehicleModel->select('type')->distinct()->orderBy('type', 'ASC')->findAll();

        // --- Departure list (paginated, searchable) ---
        $queueModel = new QueueModel();
        $builder = $this->_getFilteredBuilder($search, $fromDate, $toDate, $destFilter, $typeFilter);
        $departures = $builder->orderBy('queue.departure_time', 'DESC')->paginate(20);

        $data = [
            'title'        => 'Departure History',
            'stats'        => [
                'total'   => $totalAll,
                'today'   => $totalToday,
                'month'   => $totalMonth,
                'year'    => $totalYear,
            ],
            'departures'   => $departures,
            'destinations' => $routeModel->select('destination, vehicle_type')->distinct()->orderBy('destination', 'ASC')->findAll(),
            'vehicleTypes' => array_column($vehicleModel->select('type')->distinct()->orderBy('type', 'ASC')->findAll(), 'type'),
            'pager'        => $queueModel->pager,
            'search'       => $search,
            'from_date'    => $fromDate,
            'to_date'      => $toDate,
            'destination'  => $destFilter,
            'vehicle_type' => $typeFilter,
        ];

        return view('admin/history/index', $data);
    }

    public function print()
    {
        $search = $this->request->getGet('q');
        $fromDate = $this->request->getGet('from_date');
        $toDate = $this->request->getGet('to_date');
        $destFilter = $this->request->getGet('destination');
        $typeFilter = $this->request->getGet('vehicle_type');

        $builder = $this->_getFilteredBuilder($search, $fromDate, $toDate, $destFilter, $typeFilter);
        $results = $builder->orderBy('queue.departure_time', 'DESC')->findAll();

        $data = [
            'title'        => 'Departure History Report',
            'results'      => $results,
            'search'       => $search,
            'from_date'    => $fromDate,
            'to_date'      => $toDate,
            'destination'  => $destFilter,
            'vehicle_type' => $typeFilter,
            'is_print'     => true
        ];

        return view('admin/history/print_history', $data);
    }

    private function _getFilteredBuilder($search, $fromDate, $toDate, $destination, $vehicleType)
    {
        $queueModel = new QueueModel();
        $builder = $queueModel->select('queue.*, vehicles.plate_number, vehicles.driver_name, vehicles.owner_name, vehicles.type as vehicle_type, routes.destination, routes.origin, queue.departure_time, queue.current_passengers')
                              ->join('vehicles', 'vehicles.id = queue.vehicle_id')
                              ->join('routes', 'routes.id = queue.route_id')
                              ->where('queue.status', 'departed')
                              ->where('queue.departure_time IS NOT NULL');

        // Default to current year ONLY if no filters applied
        if (!$fromDate && !$toDate) {
            $builder->where('YEAR(queue.departure_time)', date('Y'));
        }

        if ($search) {
            $builder->groupStart()
                    ->like('vehicles.plate_number', $search)
                    ->orLike('vehicles.owner_name', $search)
                    ->orLike('vehicles.driver_name', $search)
                    ->orLike('routes.destination', $search)
                    ->orLike('routes.origin', $search)
                    ->groupEnd();
        }

        if ($fromDate) {
            $builder->where('DATE(queue.departure_time) >=', $fromDate);
        }

        if ($toDate) {
            $builder->where('DATE(queue.departure_time) <=', $toDate);
        }

        if ($destination) {
            $builder->where('routes.destination', $destination);
        }

        if ($vehicleType) {
            $builder->where('vehicles.type', $vehicleType);
        }

        return $builder;
    }
}
