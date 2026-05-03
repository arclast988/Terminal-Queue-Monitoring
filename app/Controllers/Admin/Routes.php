<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\RouteModel;
use App\Models\TerminalModel;
use App\Models\FareDiscountModel;

class Routes extends BaseController
{
    protected $routeModel;
    protected $terminalModel;
    protected $discountModel;

    public function __construct()
    {
        $this->routeModel    = new RouteModel();
        $this->terminalModel = new TerminalModel();
        $this->discountModel = new FareDiscountModel();
    }

    public function index()
    {
        // Join with terminals table to get terminal name
        $routes = $this->routeModel->select('routes.*, terminals.name as terminal_name')
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

        // Create new record
        $this->routeModel->save([
            'origin'       => $origin,
            'destination'  => $destination,
            'fare'         => $fare,
            'terminal_id'  => $terminalId,
            'vehicle_type' => $vehicleType
        ]);
        
        $this->logActivity('Create route', "$origin → $destination ($vehicleType, ₱$fare).");
        $msg = 'New route and fare added successfully.';

        return redirect()->back()->with('success', $msg);
    }

    public function edit($id)
    {
        $route = $this->routeModel->find($id);

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

        // Check if another route already exists with these new attributes (collision check)
        $collision = $this->routeModel->where('origin', $origin)
                                      ->where('destination', $destination)
                                      ->where('vehicle_type', $vehicleType)
                                      ->where('id !=', $id)
                                      ->first();
        if ($collision) {
            return redirect()->back()->withInput()->with('error', "A route already exists for $origin → $destination ($vehicleType). Update that one instead.");
        }

        $this->routeModel->update($id, [
            'origin'       => $origin,
            'destination'  => $destination,
            'fare'         => $this->request->getPost('fare'),
            'terminal_id'  => $this->request->getPost('terminal_id'),
            'vehicle_type' => $vehicleType
        ]);

        $this->logActivity('Update route', "$origin → $destination ($vehicleType).");

        return redirect()->back()->with('success', 'Route updated successfully.');
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
            'type'             => 'required|in_list[pwd,senior_citizen,student]',
            'label'            => 'required|min_length[2]|max_length[100]',
            'discount_percent' => 'required|decimal|greater_than_equal_to[0]|less_than_equal_to[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/fares')->with('errors', $this->validator->getErrors());
        }

        $this->discountModel->save([
            'type'             => $this->request->getPost('type'),
            'label'            => $this->request->getPost('label'),
            'discount_percent' => $this->request->getPost('discount_percent'),
            'is_active'        => 1,
        ]);

        $this->logActivity('Add discount', $this->request->getPost('type') . ' discount added at ' . $this->request->getPost('discount_percent') . '%.');

        return redirect()->to('/fares')->with('success', 'Discount added successfully.');
    }
}
