<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\QueueModel;
use App\Models\VehicleTypeModel;

class History extends BaseController
{
    public function index()
    {
        $queueModel = new QueueModel();
        // Automatically enforce retention policy
        $queueModel->purgeOldDepartures(departure_retention_days());

        $search = $this->request->getGet('q');
        $fromDate = $this->request->getGet('from_date');
        $toDate = $this->request->getGet('to_date');
        $destFilter = $this->request->getGet('destination');
        $typeFilter = $this->request->getGet('vehicle_type');

        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd   = date('Y-m-d 23:59:59');
        $monthStart = date('Y-m-01 00:00:00');
        $yearStart  = date('Y-01-01 00:00:00');

        // --- Stats (single query with sargable range predicates) ---
        $db = \Config\Database::connect();
        $statsResult = $db->table('queue')
            ->select("
                COUNT(*) as total,
                SUM(CASE WHEN departure_time >= '{$todayStart}' AND departure_time <= '{$todayEnd}' THEN 1 ELSE 0 END) as today,
                SUM(CASE WHEN departure_time >= '{$monthStart}' THEN 1 ELSE 0 END) as month,
                SUM(CASE WHEN departure_time >= '{$yearStart}' THEN 1 ELSE 0 END) as year
            ")
            ->where('status', 'departed')
            ->where('departure_time IS NOT NULL')
            ->get()
            ->getRow();

        $totalAll   = (int) ($statsResult->total ?? 0);
        $totalToday = (int) ($statsResult->today ?? 0);
        $totalMonth = (int) ($statsResult->month ?? 0);
        $totalYear  = (int) ($statsResult->year ?? 0);

        // --- Options for Filter Modal ---
        $routeModel = new \App\Models\RouteModel();
        $vehicleTypeModel = new VehicleTypeModel();
        
        $destinations = $routeModel->select('destination')->distinct()->orderBy('destination', 'ASC')->findAll();
        $destinationVehicleTypes = [];
        foreach ($routeModel->select('destination, vehicle_type')->distinct()->findAll() as $route) {
            $destinationVehicleTypes[$route['destination']][] = $route['vehicle_type'];
        }
        $vehicleTypes = $vehicleTypeModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll();

        // --- Departure list (paginated, searchable) ---
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
            'destinations' => $destinations,
            'destinationVehicleTypes' => $destinationVehicleTypes,
            'vehicleTypes' => $vehicleTypes,
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
        $queueModel = new QueueModel();
        $queueModel->purgeOldDepartures(departure_retention_days());

        $search = $this->request->getGet('q');
        $fromDate = $this->request->getGet('from_date');
        $toDate = $this->request->getGet('to_date');
        $destFilter = $this->request->getGet('destination');
        $typeFilter = $this->request->getGet('vehicle_type');

        $builder = $this->_getFilteredBuilder($search, $fromDate, $toDate, $destFilter, $typeFilter);
        // Cap print output to avoid OOM on large history tables.
        $results = $builder->orderBy('queue.departure_time', 'DESC')->limit(5000)->findAll();

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
        $builder = $queueModel->select('
            queue.*, 
            COALESCE(NULLIF(queue.plate_number, \'\'), vehicles.plate_number) as plate_number, 
            COALESCE(NULLIF(queue.driver_name, \'\'), vehicles.driver_name) as driver_name, 
            COALESCE(NULLIF(queue.operator_name, \'\'), NULLIF(vehicles.operator_name, \'\'), vehicles.owner_name) as operator_name,
            vehicles.owner_name, 
            vehicles.type as vehicle_type, 
            vehicles.photo as vehicle_photo, 
            routes.destination, 
            terminals.name as origin, 
            queue.departure_time, 
            queue.current_passengers
        ')
        ->withFullJoins()
        ->where('queue.status', 'departed')
        ->where('queue.departure_time IS NOT NULL');

        if ($search) {
            $builder->groupStart()
                    ->like('COALESCE(NULLIF(queue.plate_number, \'\'), vehicles.plate_number)', $search)
                    ->orLike('COALESCE(NULLIF(queue.driver_name, \'\'), vehicles.driver_name)', $search)
                    ->orLike('COALESCE(NULLIF(queue.operator_name, \'\'), NULLIF(vehicles.operator_name, \'\'), vehicles.owner_name)', $search)
                    ->orLike('routes.destination', $search)
                    ->orLike('terminals.name', $search)
                    ->groupEnd();
        }

        if ($fromDate) {
            $builder->where('queue.departure_time >=', $fromDate . ' 00:00:00');
        }

        if ($toDate) {
            $builder->where('queue.departure_time <=', $toDate . ' 23:59:59');
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
