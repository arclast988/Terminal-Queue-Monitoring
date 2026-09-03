<?php

namespace App\Controllers;

use App\Models\RouteModel;
use App\Models\AnnouncementModel;
use App\Models\TerminalModel;
use App\Models\FareDiscountModel;
use App\Models\VehicleTypeModel;

class Fares extends BaseController
{
    private function routesByVehicleType(string $vehicleType, array $discounts): array
    {
        $routeModel = new RouteModel();
        $rows = $routeModel
            ->withOrigin()
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

        $vehicleTypes = (new VehicleTypeModel())->where('is_active', 1)->orderBy('name', 'ASC')->findAll();
        $routesByType = [];
        foreach ($vehicleTypes as $vehicleType) {
            $routesByType[$vehicleType['slug']] = $this->routesByVehicleType($vehicleType['slug'], $discounts);
        }

        // Kept for the public fares view and existing API consumers.
        $van_routes     = $routesByType['van'] ?? [];
        $jeepney_routes = $routesByType['jeepney'] ?? [];
        $minibus_routes = $routesByType['minibus'] ?? [];

        $announcements = [];
        try {
            $announcements = $this->getActiveAnnouncements();
        } catch (\Throwable $e) {}

        $terminals = [];
        $all_locations = [];

        if (session()->get('isLoggedIn') && in_array(session()->get('role'), ['super_admin', 'admin'], true)) {
            $terminalModel  = new TerminalModel();
            $terminals      = $terminalModel->findAll();

            $db            = \Config\Database::connect();
            $destsRaw      = $db->query("SELECT DISTINCT destination FROM routes WHERE destination IS NOT NULL AND destination != '' ORDER BY destination ASC")->getResultArray();
            $destinations  = array_column($destsRaw, 'destination');
            $all_locations = array_values(array_filter(array_map('strtoupper', array_unique($destinations))));
            sort($all_locations);
        }

        $data = [
            'title'         => 'Route Fares',
            'body_class'    => session()->get('isLoggedIn') ? '' : 'public-page',
            'van_routes'    => $van_routes,
            'jeepney_routes'=> $jeepney_routes,
            'minibus_routes'=> $minibus_routes,
            'vehicleTypes'  => $vehicleTypes,
            'routesByType'  => $routesByType,
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

        // Cache for a few seconds: the guest fares page polls every 3s, so this
        // keeps data fresh while collapsing load to one DB query per window.
        // Version the key because the response now also carries configured vehicle types.
        $payload = cache('rt_fares_api_v2');
        if (! is_array($payload)) {
            $discountModel = new FareDiscountModel();
            $discounts = $discountModel
                ->select('fare_discounts.*, terminals.name as terminal_name')
                ->join('terminals', 'terminals.id = fare_discounts.terminal_id', 'left')
                ->where('fare_discounts.is_active', 1)
                ->where('fare_discounts.type !=', 'regular')
                ->orderBy('fare_discounts.terminal_id', 'ASC')
                ->orderBy('fare_discounts.type', 'ASC')
                ->findAll();

            $vehicleTypes = (new VehicleTypeModel())
                ->where('is_active', 1)
                ->orderBy('name', 'ASC')
                ->findAll();
            foreach ($vehicleTypes as &$vehicleType) {
                $vehicleType['image'] = vehicle_type_image($vehicleType['slug']);
            }
            unset($vehicleType);
            $routesByType = [];
            foreach ($vehicleTypes as $vehicleType) {
                $slug = $vehicleType['slug'];
                $routesByType[$slug] = $this->routesByVehicleType($slug, $discounts);
            }

            $payload = [
                'van_routes'     => $this->routesByVehicleType('van', $discounts),
                'jeepney_routes' => $this->routesByVehicleType('jeepney', $discounts),
                'minibus_routes' => $this->routesByVehicleType('minibus', $discounts),
                'vehicle_types'  => $vehicleTypes,
                'routes_by_type' => $routesByType,
                'discounts'      => $discounts,
            ];

            cache()->save('rt_fares_api_v2', $payload, 3);
        }

        // Always read the sync token live so clients keep detecting changes.
        $payload['sync_token'] = @file_get_contents(WRITEPATH . 'sync_token.txt') ?: '0';

        return $this->response
            ->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', '0')
            ->setJSON($payload);
    }
}
