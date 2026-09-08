<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VehicleModel;
use App\Models\RouteModel;
use App\Models\VehicleTypeModel;

class Vehicles extends BaseController
{
    protected $vehicleModel;
    protected $routeModel;
    protected $vehicleTypeModel;

    public function __construct()
    {
        $this->vehicleModel = new VehicleModel();
        $this->routeModel = new RouteModel();
        $this->vehicleTypeModel = new VehicleTypeModel();
    }

    public function index()
    {
        // Join routes to get assigned route info
        $vehicles = $this->vehicleModel
            ->select('vehicles.*, terminals.name as route_origin, routes.destination as route_destination, routes.vehicle_type as route_vehicle_type')
            ->join('routes', 'routes.id = vehicles.default_route_id', 'left')
            ->join('terminals', 'terminals.id = routes.terminal_id', 'left')
            ->orderBy('vehicles.created_at', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Vehicle Register',
            'vehicles' => $vehicles,
            'routes' => $this->routeModel->withOrigin()->orderBy('destination', 'ASC')->findAll(),
            'vehicleTypes' => $this->vehicleTypeModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll(),
        ];

        return view('admin/vehicles/index', $data);
    }

    /**
     * AJAX endpoint for real-time duplicate plate detection and length validation.
     */
    public function checkPlate()
    {
        $plate = strtoupper(trim((string)$this->request->getGet('plate')));
        $excludeId = (int)$this->request->getGet('exclude_id');

        if ($plate === '') {
            return $this->response->setJSON([
                'valid'     => false,
                'available' => true,
                'status'    => 'empty',
                'message'   => '',
            ]);
        }

        if (strlen($plate) < 5) {
            return $this->response->setJSON([
                'valid'     => false,
                'available' => false,
                'status'    => 'too_short',
                'message'   => 'Plate number must be at least 5 characters.',
            ]);
        }

        if (strlen($plate) > 20) {
            return $this->response->setJSON([
                'valid'     => false,
                'available' => false,
                'status'    => 'too_long',
                'message'   => 'Plate number cannot exceed 20 characters.',
            ]);
        }

        $query = $this->vehicleModel->where('LOWER(plate_number)', strtolower($plate));
        if ($excludeId > 0) {
            $query->where('id !=', $excludeId);
        }

        $existing = $query->first();

        if ($existing) {
            return $this->response->setJSON([
                'valid'     => true,
                'available' => false,
                'status'    => 'duplicate',
                'message'   => 'This plate number is already registered. Please enter a different plate number.',
            ]);
        }

        return $this->response->setJSON([
            'valid'     => true,
            'available' => true,
            'status'    => 'available',
            'message'   => 'Plate number is available.',
        ]);
    }

    public function store()
    {
        $rawPlate = strtoupper(trim((string)($this->request->getPost('plate_number') ?? '')));
        $rawOperator = strtoupper(trim((string)($this->request->getPost('operator_name') ?? '')));
        $_POST['plate_number'] = $rawPlate;
        $_POST['operator_name'] = $rawOperator;

        $rules = [
            'plate_number'  => [
                'rules'  => 'required|min_length[5]|max_length[20]|is_unique[vehicles.plate_number]',
                'errors' => [
                    'required'   => 'Please enter a plate number.',
                    'min_length' => 'Plate number must be at least 5 characters.',
                    'max_length' => 'Plate number cannot exceed 20 characters.',
                    'is_unique'  => 'This plate number is already registered. Please enter a different plate number.',
                ],
            ],
            'operator_name' => [
                'rules'  => 'required|min_length[2]|max_length[100]',
                'errors' => [
                    'required'   => 'Please enter the operator name.',
                    'min_length' => 'Operator name must be at least 2 characters.',
                    'max_length' => 'Operator name cannot exceed 100 characters.',
                ],
            ],
            'driver_name'   => [
                'rules'  => 'required|min_length[3]|max_length[100]',
                'errors' => [
                    'required'   => 'Please enter the driver name.',
                    'min_length' => 'Driver name must be at least 3 characters.',
                    'max_length' => 'Driver name cannot exceed 100 characters.',
                ],
            ],
            'type'          => [
                'rules'  => 'required|max_length[50]',
                'errors' => [
                    'required'   => 'Please select a vehicle type.',
                    'max_length' => 'Vehicle type exceeds maximum allowed length.',
                ],
            ],
            'capacity'      => [
                'rules'  => 'required|integer|greater_than[0]',
                'errors' => [
                    'required'     => 'Please enter passenger capacity.',
                    'integer'      => 'Capacity must be a valid number.',
                    'greater_than' => 'Capacity must be at least 1 passenger.',
                ],
            ],
            'route_id'      => [
                'rules'  => 'required|integer',
                'errors' => [
                    'required' => 'Please select a destination route.',
                    'integer'  => 'Please select a valid destination route.',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Additional case-insensitive plate uniqueness check
        $existing = $this->vehicleModel
            ->where('LOWER(plate_number)', strtolower($rawPlate))
            ->first();
        if ($existing) {
            return redirect()->back()->withInput()->with('errors', [
                'plate_number' => 'This plate number is already registered. Please enter a different plate number.'
            ]);
        }

        $routeId = $this->request->getPost('route_id');
        $route = $this->routeModel->withOrigin()->find($routeId);

        // Validate vehicle type matches route type
        $vehicleType = $this->request->getPost('type');
        if (!$this->vehicleTypeModel->where('slug', $vehicleType)->where('is_active', 1)->first()) {
            return redirect()->back()->withInput()->with('error', 'Please select a valid active vehicle type.');
        }
        if ($route && $route['vehicle_type'] !== $vehicleType) {
            return redirect()->back()->withInput()->with('error', 'Vehicle type (' . ucfirst($vehicleType) . ') must match the route type (' . ucfirst($route['vehicle_type']) . ').');
        }

        // Driver and Operator must not be the same person
        $driverName = trim($this->request->getPost('driver_name'));
        $operatorName = $rawOperator;
        if (strtolower($driverName) === strtolower($operatorName)) {
            return redirect()->back()->withInput()->with('error', 'Driver and Operator must not be the same person.');
        }

        $this->vehicleModel->save([
            'plate_number'  => $rawPlate,
            'operator_name' => $rawOperator,
            'owner_name'    => $rawOperator,
            'driver_name'   => $driverName,
            'type'          => $vehicleType,
            'capacity'      => $this->request->getPost('capacity'),
            'status'        => 'active',
            'default_route_id' => $routeId,
        ]);

        $routeLabel = $route ? (strtoupper($route['origin']) . ' → ' . strtoupper($route['destination'])) : 'N/A';
        $this->logActivity('Assign vehicle to route', 'Registered vehicle ' . $rawPlate . ' (' . $vehicleType . ') - Operator: ' . $rawOperator . ' - Driver: ' . $driverName . ' - Route: ' . $routeLabel);
        $this->broadcastUpdate('queue_update', ['action' => 'vehicle_created']);

        return redirect()->to('/admin/vehicles')->with('success', 'Vehicle registered successfully.');
    }

    public function edit($id)
    {
        $vehicle = $this->vehicleModel->find($id);
        if (!$vehicle) {
            return redirect()->to('/admin/vehicles')->with('error', 'Vehicle not found.');
        }

        $data = [
            'title' => 'Edit Vehicle',
            'vehicle' => $vehicle,
            'routes' => $this->routeModel->withOrigin()->orderBy('destination', 'ASC')->findAll(),
            'vehicleTypes' => $this->vehicleTypeModel->where('is_active', 1)->orderBy('name', 'ASC')->findAll(),
        ];

        return view('admin/vehicles/edit', $data);
    }

    public function update($id)
    {
        $vehicle = $this->vehicleModel->find($id);
        if (!$vehicle) {
            return redirect()->to('/admin/vehicles')->with('error', 'Vehicle not found.');
        }

        $rawPlate = strtoupper(trim((string)($this->request->getPost('plate_number') ?? '')));
        $rawOperator = strtoupper(trim((string)($this->request->getPost('operator_name') ?? '')));
        $_POST['plate_number'] = $rawPlate;
        $_POST['operator_name'] = $rawOperator;

        $rules = [
            'plate_number'  => [
                'rules'  => 'required|min_length[5]|max_length[20]|is_unique[vehicles.plate_number,id,' . $id . ']',
                'errors' => [
                    'required'   => 'Please enter a plate number.',
                    'min_length' => 'Plate number must be at least 5 characters.',
                    'max_length' => 'Plate number cannot exceed 20 characters.',
                    'is_unique'  => 'This plate number is already registered to another vehicle.',
                ],
            ],
            'operator_name' => [
                'rules'  => 'required|min_length[2]|max_length[100]',
                'errors' => [
                    'required'   => 'Please enter the operator name.',
                    'min_length' => 'Operator name must be at least 2 characters.',
                    'max_length' => 'Operator name cannot exceed 100 characters.',
                ],
            ],
            'driver_name'   => [
                'rules'  => 'required|min_length[3]|max_length[100]',
                'errors' => [
                    'required'   => 'Please enter the driver name.',
                    'min_length' => 'Driver name must be at least 3 characters.',
                    'max_length' => 'Driver name cannot exceed 100 characters.',
                ],
            ],
            'type'          => [
                'rules'  => 'required|max_length[50]',
                'errors' => [
                    'required'   => 'Please select a vehicle type.',
                    'max_length' => 'Vehicle type exceeds maximum allowed length.',
                ],
            ],
            'capacity'      => [
                'rules'  => 'required|integer|greater_than[0]',
                'errors' => [
                    'required'     => 'Please enter passenger capacity.',
                    'integer'      => 'Capacity must be a valid number.',
                    'greater_than' => 'Capacity must be at least 1 passenger.',
                ],
            ],
            'status'        => [
                'rules'  => 'required|in_list[active,maintenance]',
                'errors' => [
                    'required' => 'Please select a vehicle status.',
                    'in_list'  => 'Vehicle status must be either active or maintenance.',
                ],
            ],
            'route_id'      => [
                'rules'  => 'required|integer',
                'errors' => [
                    'required' => 'Please select a destination route.',
                    'integer'  => 'Please select a valid destination route.',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Additional case-insensitive plate uniqueness check
        $existing = $this->vehicleModel
            ->where('LOWER(plate_number)', strtolower($rawPlate))
            ->where('id !=', $id)
            ->first();
        if ($existing) {
            return redirect()->back()->withInput()->with('errors', [
                'plate_number' => 'This plate number is already registered to another vehicle.'
            ]);
        }

        $routeId = $this->request->getPost('route_id');
        $route = $this->routeModel->withOrigin()->find($routeId);
        $vehicleType = $this->request->getPost('type');

        if (!$this->vehicleTypeModel->where('slug', $vehicleType)->where('is_active', 1)->first()) {
            return redirect()->back()->withInput()->with('error', 'Please select a valid active vehicle type.');
        }

        if ($route && $route['vehicle_type'] !== $vehicleType) {
            return redirect()->back()->withInput()->with('error', 'Vehicle type (' . ucfirst($vehicleType) . ') must match the route type (' . ucfirst($route['vehicle_type']) . ').');
        }

        // Driver and Operator must not be the same person
        $driverName = trim($this->request->getPost('driver_name'));
        $operatorName = $rawOperator;
        if (strtolower($driverName) === strtolower($operatorName)) {
            return redirect()->back()->withInput()->with('error', 'Driver and Operator must not be the same person.');
        }

        if ($this->inputsUnchanged([
            'plate_number'     => strtoupper(trim((string) ($vehicle['plate_number'] ?? ''))),
            'operator_name'    => strtoupper(trim((string) ($vehicle['operator_name'] ?? ''))),
            'driver_name'      => trim((string) ($vehicle['driver_name'] ?? '')),
            'type'             => $vehicle['type'] ?? '',
            'capacity'         => $vehicle['capacity'] ?? '',
            'status'           => $vehicle['status'] ?? '',
            'default_route_id' => $vehicle['default_route_id'] ?? $vehicle['route_id'] ?? '',
        ], [
            'plate_number'     => $rawPlate,
            'operator_name'    => $rawOperator,
            'driver_name'      => $driverName,
            'type'             => $vehicleType,
            'capacity'         => $this->request->getPost('capacity'),
            'status'           => $this->request->getPost('status'),
            'default_route_id' => $routeId,
        ])) {
            return $this->noChangesResponse();
        }

        $this->vehicleModel->update($id, [
            'plate_number'  => $rawPlate,
            'operator_name' => $rawOperator,
            'owner_name'    => $rawOperator,
            'driver_name'   => $driverName,
            'type'          => $vehicleType,
            'capacity'      => $this->request->getPost('capacity'),
            'status'        => $this->request->getPost('status'),
            'default_route_id' => $routeId,
        ]);

        // Log route change details if route was reassigned
        $oldRouteId = $vehicle['default_route_id'];
        if ((string) $oldRouteId !== (string) $routeId) {
            $oldRoute = $oldRouteId ? $this->routeModel->withOrigin()->find($oldRouteId) : null;
            $oldLabel = $oldRoute ? (strtoupper($oldRoute['origin']) . ' → ' . strtoupper($oldRoute['destination'])) : 'None';
            $newLabel = $route ? (strtoupper($route['origin']) . ' → ' . strtoupper($route['destination'])) : 'None';
            $this->logActivity('Reassign vehicle route', 'Reassigned ' . $rawPlate . ' from ' . $oldLabel . ' to ' . $newLabel);
        } else {
            $this->logActivity('Update vehicle', 'Updated vehicle ' . $rawPlate);
        }

        $this->broadcastUpdate('queue_update', ['action' => 'vehicle_updated', 'id' => (int) $id]);

        return redirect()->to('/admin/vehicles')->with('success', 'Vehicle updated successfully.');
    }

    public function delete($id)
    {
        $vehicle = $this->vehicleModel->find($id);
        if ($this->vehicleModel->delete($id)) {
            if ($vehicle) {
                $this->logActivity('Delete vehicle', 'Deleted vehicle ' . $vehicle['plate_number'] . '.');
            }
            $this->broadcastUpdate('queue_update', ['action' => 'vehicle_deleted', 'id' => (int) $id]);
            return redirect()->to('/admin/vehicles')->with('success', 'Vehicle deleted successfully.');
        }
        return redirect()->to('/admin/vehicles')->with('error', 'Failed to delete vehicle.');
    }
}
