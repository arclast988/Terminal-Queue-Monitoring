<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\RouteModel;
use App\Models\TerminalModel;
use App\Models\FareDiscountModel;
use App\Models\FareModel;

class Routes extends BaseController
{
    protected $routeModel;
    protected $terminalModel;
    protected $discountModel;
    protected $fareModel;

    public function __construct()
    {
        $this->routeModel    = new RouteModel();
        $this->terminalModel = new TerminalModel();
        $this->discountModel = new FareDiscountModel();
        $this->fareModel     = new FareModel();
    }

    public function index()
    {
        // Join with terminals table to get terminal name
        $routes = $this->routeModel->select('routes.*, fares.amount AS fare, terminals.name as terminal_name')
                                   ->join('fares', 'fares.route_id = routes.id', 'left')
                                   ->join('terminals', 'terminals.id = routes.terminal_id')
                                   ->findAll();

        $data = [
            'title'  => 'Manage Routes',
            'routes' => $routes
        ];

        return view('admin/routes/index', $data);
    }

    /**
     * Get distinct origins and destinations from the routes table for dropdown population.
     */
    private function getDistinctLocations(): array
    {
        $db = \Config\Database::connect();

        $originsResult = $db->query('SELECT DISTINCT origin FROM routes ORDER BY origin ASC')->getResultArray();
        $destsResult   = $db->query('SELECT DISTINCT destination FROM routes ORDER BY destination ASC')->getResultArray();

        // Merge into a single sorted unique list for locations
        $origins      = array_column($originsResult, 'origin');
        $destinations = array_column($destsResult, 'destination');
        $allLocations = array_unique(array_merge($origins, $destinations));
        sort($allLocations);

        return [
            'origins'       => $origins,
            'destinations'  => $destinations,
            'all_locations' => $allLocations,
        ];
    }

    public function create()
    {
        $locations = $this->getDistinctLocations();

        $data = [
            'title'         => 'Add New Route',
            'terminals'     => $this->terminalModel->findAll(),
            'origins'       => $locations['origins'],
            'destinations'  => $locations['destinations'],
            'all_locations' => $locations['all_locations'],
        ];
        return view('admin/routes/create', $data);
    }

    public function store()
    {
        $rules = [
            'origin'       => 'required|min_length[2]|max_length[100]',
            'destination'  => 'required|min_length[2]|max_length[100]',
            'fare'         => 'required|decimal|greater_than[0]',
            'terminal_id'  => 'required|integer|is_not_unique[terminals.id]',
            'vehicle_type' => 'required|in_list[jeepney,van,minibus]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $origin      = strtoupper(trim($this->request->getPost('origin')));
        $destination = strtoupper(trim($this->request->getPost('destination')));
        $vehicleType = $this->request->getPost('vehicle_type');
        $fare        = $this->request->getPost('fare');
        $terminalId  = $this->request->getPost('terminal_id');

        // Check for existing route with same (Origin, Destination, VehicleType)
        $existing = $this->routeModel->where('origin', $origin)
                                     ->where('destination', $destination)
                                     ->where('vehicle_type', $vehicleType)
                                     ->first();

        if ($existing) {
            return redirect()->back()->withInput()->with('error', "This route ($origin → $destination) already exists for " . ucfirst($vehicleType) . ". Please edit the existing one instead of adding a new one.");
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $routeId = $this->routeModel->insert([
            'origin'       => $origin,
            'destination'  => $destination,
            'terminal_id'  => $terminalId,
            'vehicle_type' => $vehicleType
        ], true);

        $this->fareModel->insert([
            'route_id' => $routeId,
            'amount'   => $fare,
        ]);

        $db->transComplete();

        if (!$db->transStatus()) {
            return redirect()->back()->withInput()->with('error', 'Failed to add route and fare.');
        }
        
        $this->logActivity('Create route', "$origin → $destination ($vehicleType, ₱$fare).");
        $msg = 'New route and fare added successfully.';

        return redirect()->back()->with('success', $msg);
    }

    public function edit($id)
    {
        $route = $this->routeModel->withFare()->find($id);

        if (!$route) {
            return redirect()->to('/admin/routes')->with('error', 'Route not found.');
        }

        $locations = $this->getDistinctLocations();

        $data = [
            'title'         => 'Edit Route',
            'route'         => $route,
            'terminals'     => $this->terminalModel->findAll(),
            'origins'       => $locations['origins'],
            'destinations'  => $locations['destinations'],
            'all_locations' => $locations['all_locations'],
        ];

        return view('admin/routes/edit', $data);
    }

    public function update($id)
    {
        $existingRoute = $this->routeModel->withFare()->find($id);

        if (!$existingRoute) {
            return redirect()->to('/admin/routes')->with('error', 'Route not found.');
        }

        $rules = [
            'origin'       => 'required|min_length[2]|max_length[100]',
            'destination'  => 'required|min_length[2]|max_length[100]',
            'fare'         => 'required|decimal|greater_than[0]',
            'terminal_id'  => 'required|integer|is_not_unique[terminals.id]',
            'vehicle_type' => 'required|in_list[jeepney,van,minibus]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $origin      = strtoupper(trim($this->request->getPost('origin')));
        $destination = strtoupper(trim($this->request->getPost('destination')));
        $vehicleType = $this->request->getPost('vehicle_type');
        $newFare     = $this->request->getPost('fare');

        // Check if another route already exists with these new attributes (collision check)
        $collision = $this->routeModel->where('origin', $origin)
                                      ->where('destination', $destination)
                                      ->where('vehicle_type', $vehicleType)
                                      ->where('id !=', $id)
                                      ->first();
        if ($collision) {
            return redirect()->back()->withInput()->with('error', "A route already exists for $origin → $destination ($vehicleType). Update that one instead.");
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $this->routeModel->update($id, [
            'origin'       => $origin,
            'destination'  => $destination,
            'terminal_id'  => $this->request->getPost('terminal_id'),
            'vehicle_type' => $vehicleType
        ]);

        $this->fareModel->upsertForRoute((int) $id, $newFare);

        $db->transComplete();

        if (!$db->transStatus()) {
            return redirect()->back()->withInput()->with('error', 'Failed to update route and fare.');
        }

        $oldFare      = (float) ($existingRoute['fare'] ?? 0);
        $newFareFloat = (float) $newFare;

        if (abs($oldFare - $newFareFloat) > 0.00001) {
            $this->logActivity(
                'Update fare',
                sprintf(
                    '%s route fare changed. Before: PHP %s | After: PHP %s.',
                    strtoupper($existingRoute['origin'] . ' to ' . $existingRoute['destination'] . ' (' . $existingRoute['vehicle_type'] . ')'),
                    number_format($oldFare, 2),
                    number_format($newFareFloat, 2)
                )
            );
        } else {
            $this->logActivity('Update route', $origin . ' to ' . $destination . ' (' . $vehicleType . ').');
        }

        return redirect()->back()->with('success', 'Route updated successfully.');

        $this->logActivity('Update route', "$origin → $destination ($vehicleType).");

    }

    public function delete($id)
    {
        $route = $this->routeModel->find($id);
        if ($this->routeModel->delete($id)) {
            if ($route) {
                $this->logActivity('Delete route', $route['origin'] . ' → ' . $route['destination'] . '.');
            }
            return redirect()->back()->with('success', 'Route deleted successfully.');
        }
        return redirect()->back()->with('error', 'Failed to delete route.');
    }

    // ──────────────────────────────────────────────
    //  Discount Management
    // ──────────────────────────────────────────────

    /**
     * Update a discount record (percentage + label).
     */
    public function updateDiscount($id)
    {
        $discount = $this->discountModel->find($id);

        if (!$discount) {
            return redirect()->to('/fares')->with('error', 'Discount record not found.');
        }

        $rules = [
            'discount_percent' => 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
            'label'            => 'required|min_length[2]|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/fares')->with('errors', $this->validator->getErrors());
        }

        $this->discountModel->update($id, [
            'discount_percent' => $this->request->getPost('discount_percent'),
            'label'            => $this->request->getPost('label'),
            'is_active'        => $this->request->getPost('is_active') ? 1 : 0,
        ]);

        $this->logActivity('Update discount', $discount['type'] . ' discount updated to ' . $this->request->getPost('discount_percent') . '%.');

        return redirect()->to('/fares')->with('success', 'Discount updated successfully.');
    }

    /**
     * Add a new discount type (for custom types beyond PWD/Senior/Student).
     */
    public function storeDiscount()
    {
        $rules = [
            'type'             => 'required|min_length[2]|max_length[50]|alpha_dash',
            'label'            => 'required|min_length[2]|max_length[100]',
            'discount_percent' => 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/fares')->with('errors', $this->validator->getErrors());
        }

        $type = strtolower(trim($this->request->getPost('type')));

        // Prevent duplicate types
        $existing = $this->discountModel->where('type', $type)->first();
        if ($existing) {
            return redirect()->to('/fares')->with('error', 'A discount with type "' . $type . '" already exists. Please edit the existing one instead.');
        }

        $this->discountModel->save([
            'type'             => $type,
            'label'            => $this->request->getPost('label'),
            'discount_percent' => $this->request->getPost('discount_percent'),
            'is_active'        => 1,
        ]);

        $this->logActivity('Add discount', $this->request->getPost('label') . ' discount added at ' . $this->request->getPost('discount_percent') . '%.');

        return redirect()->to('/fares')->with('success', 'Discount added successfully.');
    }

    /**
     * Delete a discount record.
     */
    public function deleteDiscount($id)
    {
        $discount = $this->discountModel->find($id);

        if (!$discount) {
            return redirect()->to('/fares')->with('error', 'Discount record not found.');
        }

        $this->discountModel->delete($id);

        $this->logActivity('Delete discount', $discount['type'] . ' discount (' . $discount['label'] . ') deleted.');

        return redirect()->to('/fares')->with('success', 'Discount deleted successfully.');
    }
}
