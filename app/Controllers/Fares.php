<?php

namespace App\Controllers;

use App\Models\RouteModel;
use App\Models\AnnouncementModel;
use App\Models\TerminalModel;
use App\Models\FareDiscountModel;

class Fares extends BaseController
{
    private function routesByVehicleType(string $vehicleType, array $discounts): array
    {
        $routeModel = new RouteModel();
        $rows = $routeModel
            ->select('routes.*, terminals.name as origin')
            ->join('terminals', 'terminals.id = routes.terminal_id')
            ->where('vehicle_type', $vehicleType)
            ->orderBy('destination', 'ASC')
            ->findAll();

        return enrich_routes_with_discounts($rows, $discounts);
    }

    public function index()
    {
        helper('fare');

        $discountModel = new FareDiscountModel();
        $discounts = $discountModel
            ->select('fare_discounts.*, terminals.name as terminal_name')
            ->join('terminals', 'terminals.id = fare_discounts.terminal_id', 'left')
            ->where('fare_discounts.is_active', 1)
            ->where('fare_discounts.type !=', 'regular')
            ->orderBy('fare_discounts.terminal_id', 'ASC')
            ->orderBy('fare_discounts.type', 'ASC')
            ->findAll();

        $van_routes     = $this->routesByVehicleType('van', $discounts);
        $jeepney_routes = $this->routesByVehicleType('jeepney', $discounts);
        $minibus_routes = $this->routesByVehicleType('minibus', $discounts);

        $announcements = [];
        try {
            $announcementModel = new AnnouncementModel();
            $announcements     = $announcementModel->where('is_active', 1)->orderBy('sort_order', 'ASC')->findAll();
        } catch (\Throwable $e) {}

        $terminals = [];
        $all_locations = [];

        if (session()->get('isLoggedIn') && session()->get('role') === 'admin') {
            $terminalModel  = new TerminalModel();
            $terminals      = $terminalModel->findAll();

            $db            = \Config\Database::connect();
            $destsRaw      = $db->query('SELECT DISTINCT destination FROM routes ORDER BY destination ASC')->getResultArray();
            $destinations  = array_column($destsRaw, 'destination');
            $all_locations = array_unique($destinations);
            sort($all_locations);
        }

        $data = [
            'title'         => 'Route Fares',
            'body_class'    => 'public-page',
            'van_routes'    => $van_routes,
            'jeepney_routes'=> $jeepney_routes,
            'minibus_routes'=> $minibus_routes,
            'announcements' => $announcements,
            'terminals'     => $terminals,
            'discounts'     => $discounts,
            'all_locations' => $all_locations,
        ];

        if (session()->get('isLoggedIn')) {
            return view('shared/fares', $data);
        }
        return view('public/fares', $data);
    }

    public function apiData()
    {
        helper('fare');

        $discountModel = new FareDiscountModel();
        $discounts = $discountModel
            ->select('fare_discounts.*, terminals.name as terminal_name')
            ->join('terminals', 'terminals.id = fare_discounts.terminal_id', 'left')
            ->where('fare_discounts.is_active', 1)
            ->where('fare_discounts.type !=', 'regular')
            ->orderBy('fare_discounts.terminal_id', 'ASC')
            ->orderBy('fare_discounts.type', 'ASC')
            ->findAll();

        $van_routes     = $this->routesByVehicleType('van', $discounts);
        $jeepney_routes = $this->routesByVehicleType('jeepney', $discounts);
        $minibus_routes = $this->routesByVehicleType('minibus', $discounts);

        return $this->response
            ->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0')
            ->setJSON([
                'van_routes'     => $van_routes,
                'jeepney_routes' => $jeepney_routes,
                'minibus_routes' => $minibus_routes,
                'discounts'      => $discounts,
            ]);
    }
}
