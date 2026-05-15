<?php

namespace App\Controllers;

use App\Models\RouteModel;
use App\Models\AnnouncementModel;
use App\Models\TerminalModel;
use App\Models\FareDiscountModel;

class Fares extends BaseController
{
    private function routesByVehicleType(string $vehicleType): array
    {
        $routeModel = new RouteModel();

        return $routeModel->withFare()
            ->where('routes.vehicle_type', $vehicleType)
            ->findAll();
    }

    public function index()
    {
        // Fetch routes grouped by vehicle type
        $van_routes     = $this->routesByVehicleType('van');
        $jeepney_routes = $this->routesByVehicleType('jeepney');
        $minibus_routes = $this->routesByVehicleType('minibus');

        $announcements = [];
        try {
            $announcementModel = new AnnouncementModel();
            $announcements     = $announcementModel->where('is_active', 1)->orderBy('sort_order', 'ASC')->findAll();
        } catch (\Throwable $e) {}

        // --- Data for admin/staff management ---
        $terminals = [];
        $all_locations = [];

        // Always load discounts — shown to public too (view-only)
        $discountModel = new FareDiscountModel();
        $discounts     = $discountModel->where('is_active', 1)->orderBy('type', 'ASC')->findAll();

        if (session()->get('isLoggedIn') && in_array(session()->get('role'), ['admin', 'staff'])) {
            $terminalModel  = new TerminalModel();
            $terminals      = $terminalModel->findAll();

            // Collect distinct locations from DB for dropdown-constrained add form
            $db            = \Config\Database::connect();
            $originsRaw    = $db->query('SELECT DISTINCT origin FROM routes ORDER BY origin ASC')->getResultArray();
            $destsRaw      = $db->query('SELECT DISTINCT destination FROM routes ORDER BY destination ASC')->getResultArray();
            $origins       = array_column($originsRaw, 'origin');
            $destinations  = array_column($destsRaw, 'destination');
            $all_locations = array_unique(array_merge($origins, $destinations));
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

        // Use shared view for logged-in users, public view for guests
        if (session()->get('isLoggedIn')) {
            return view('shared/fares', $data);
        }
        return view('public/fares', $data);
    }

    /**
     * API endpoint: returns fare data as JSON for AJAX polling.
     */
    public function apiData()
    {
        $discountModel = new FareDiscountModel();

        $van_routes     = $this->routesByVehicleType('van');
        $jeepney_routes = $this->routesByVehicleType('jeepney');
        $minibus_routes = $this->routesByVehicleType('minibus');
        $discounts      = $discountModel->where('is_active', 1)->orderBy('type', 'ASC')->findAll();

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
