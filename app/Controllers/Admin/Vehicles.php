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

    public function store()
    {
        $rules = [
            'plate_number'  => 'required|min_length[5]|max_length[20]|is_unique[vehicles.plate_number]',
            'operator_name' => 'required|min_length[2]|max_length[100]',
            'driver_name'   => 'required|min_length[3]|max_length[100]',
            'type'          => 'required|max_length[50]',
            'capacity'      => 'required|integer|greater_than[0]',
            'route_id'      => 'required|integer',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
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
        $operatorName = trim($this->request->getPost('operator_name'));
        if (strtolower($driverName) === strtolower($operatorName)) {
            return redirect()->back()->withInput()->with('error', 'Driver and Operator must not be the same person.');
        }

        $this->vehicleModel->save([
            'plate_number'  => $this->request->getPost('plate_number'),
            'operator_name' => $this->request->getPost('operator_name'),
            'owner_name'    => $this->request->getPost('operator_name'),
            'driver_name'   => $this->request->getPost('driver_name'),
            'type'          => $vehicleType,
            'capacity'      => $this->request->getPost('capacity'),
            'status'        => 'active',
            'default_route_id' => $routeId,
        ]);

        $routeLabel = $route ? (strtoupper($route['origin']) . ' → ' . strtoupper($route['destination'])) : 'N/A';
        $this->logActivity('Assign vehicle to route', 'Registered vehicle ' . $this->request->getPost('plate_number') . ' (' . $vehicleType . ') - Operator: ' . $this->request->getPost('operator_name') . ' - Driver: ' . $this->request->getPost('driver_name') . ' - Route: ' . $routeLabel);
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

        $rules = [
            'plate_number'  => 'required|min_length[5]|max_length[20]|is_unique[vehicles.plate_number,id,' . $id . ']',
            'operator_name' => 'required|min_length[2]|max_length[100]',
            'driver_name'   => 'required|min_length[3]|max_length[100]',
            'type'          => 'required|max_length[50]',
            'capacity'      => 'required|integer|greater_than[0]',
            'status'        => 'required|in_list[active,maintenance]',
            'route_id'      => 'required|integer',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
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
        $operatorName = trim($this->request->getPost('operator_name'));
        if (strtolower($driverName) === strtolower($operatorName)) {
            return redirect()->back()->withInput()->with('error', 'Driver and Operator must not be the same person.');
        }

        $this->vehicleModel->update($id, [
            'plate_number'  => $this->request->getPost('plate_number'),
            'operator_name' => $this->request->getPost('operator_name'),
            'owner_name'    => $this->request->getPost('operator_name'),
            'driver_name'   => $this->request->getPost('driver_name'),
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
            $this->logActivity('Reassign vehicle route', 'Reassigned ' . $this->request->getPost('plate_number') . ' from ' . $oldLabel . ' to ' . $newLabel);
        } else {
            $this->logActivity('Update vehicle', 'Updated vehicle ' . $this->request->getPost('plate_number'));
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
