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
        // Automatically enforce 60-day retention policy
        $queueModel->purgeOldDepartures(60);

        $search = $this->request->getGet('q');
        $fromDate = $this->request->getGet('from_date');
        $toDate = $this->request->getGet('to_date');
        $destFilter = $this->request->getGet('destination');
        $typeFilter = $this->request->getGet('vehicle_type');

        $todayStr = date('Y-m-d');
        $monthStr = date('Y-m');
        $yearStr  = date('Y');

        // --- Stats (single query with conditional aggregation within retention) ---
        $db = \Config\Database::connect();
        $statsResult = $db->table('queue')
            ->select("
                COUNT(*) as total,
                SUM(CASE WHEN departure_time LIKE '{$todayStr}%' THEN 1 ELSE 0 END) as today,
                SUM(CASE WHEN departure_time LIKE '{$monthStr}%' THEN 1 ELSE 0 END) as month,
                SUM(CASE WHEN departure_time LIKE '{$yearStr}%' THEN 1 ELSE 0 END) as year
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
            'destinations' => $routeModel->select('destination, vehicle_type')->distinct()->orderBy('destination', 'ASC')->findAll(),
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
        $queueModel->purgeOldDepartures(60);

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

    public function export()
    {
        $queueModel = new QueueModel();
        $queueModel->purgeOldDepartures(60);

        $search = $this->request->getGet('q');
        $fromDate = $this->request->getGet('from_date');
        $toDate = $this->request->getGet('to_date');
        $destFilter = $this->request->getGet('destination');
        $typeFilter = $this->request->getGet('vehicle_type');

        $builder = $this->_getFilteredBuilder($search, $fromDate, $toDate, $destFilter, $typeFilter);
        $results = $builder->orderBy('queue.departure_time', 'DESC')->findAll();

        $filename = 'departure_history_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, ['Departure Time', 'Plate Number', 'Vehicle Type', 'Driver', 'Operator', 'Origin', 'Destination', 'Passengers']);

        foreach ($results as $row) {
            fputcsv($output, [
                date('Y-m-d h:i A', strtotime($row['departure_time'])),
                $row['plate_number'] ?? '—',
                ucfirst($row['vehicle_type'] ?? '—'),
                $row['driver_name'] ?? '—',
                $row['operator_name'] ?? $row['owner_name'] ?? '—',
                $row['origin'] ?? 'Palompon',
                $row['destination'] ?? '—',
                $row['current_passengers'] ?? 0
            ]);
        }

        fclose($output);
        exit;
    }

    private function _getFilteredBuilder($search, $fromDate, $toDate, $destination, $vehicleType)
    {
        $queueModel = new QueueModel();
        $builder = $queueModel->select('
            queue.*, 
            COALESCE(NULLIF(queue.plate_number, ""), vehicles.plate_number) as plate_number, 
            COALESCE(NULLIF(queue.driver_name, ""), vehicles.driver_name) as driver_name, 
            COALESCE(NULLIF(queue.operator_name, ""), NULLIF(vehicles.operator_name, ""), vehicles.owner_name) as operator_name, 
            vehicles.owner_name, 
            vehicles.type as vehicle_type, 
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
                    ->like('queue.plate_number', $search)
                    ->orLike('vehicles.plate_number', $search)
                    ->orLike('queue.driver_name', $search)
                    ->orLike('vehicles.driver_name', $search)
                    ->orLike('queue.operator_name', $search)
                    ->orLike('vehicles.operator_name', $search)
                    ->orLike('vehicles.owner_name', $search)
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
