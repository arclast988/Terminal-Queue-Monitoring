<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VehicleTypeModel;

class VehicleTypes extends BaseController
{
    public function store()
    {
        $name = trim((string) $this->request->getPost('name'));
        $slug = strtolower((string) preg_replace('/[^a-z0-9]+/i', '_', $name));
        $slug = trim($slug, '_');

        if ($name === '' || strlen($name) < 2 || strlen($name) > 80 || $slug === '' || strlen($slug) > 50) {
            return redirect()->to('/admin/vehicles')->with('error', 'Enter a vehicle type name between 2 and 80 characters.');
        }

        $types = new VehicleTypeModel();
        if ($types->where('slug', $slug)->first()) {
            return redirect()->to('/admin/vehicles')->with('error', 'That vehicle type already exists.');
        }

        $types->insert(['name' => $name, 'slug' => $slug, 'is_active' => 1]);
        $this->logActivity('Add vehicle type', 'Added vehicle type ' . $name . '.');
        $this->broadcastUpdate('vehicle_type_update', ['action' => 'create', 'slug' => $slug]);

        return redirect()->to('/admin/vehicles')->with('success', $name . ' was added. It is now available for vehicles and fares.');
    }

    public function delete($id)
    {
        $types = new VehicleTypeModel();
        $type  = $types->find($id);

        if (!$type) {
            return redirect()->back()->with('error', 'Vehicle type not found.');
        }

        $slug = $type['slug'];
        $name = $type['name'];

        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Find all routes with this vehicle type
        $routeModel = new \App\Models\RouteModel();
        $routes     = $routeModel->where('vehicle_type', $slug)->findAll();
        $routeIds   = array_column($routes, 'id');

        // 2. Find all vehicles with this vehicle type or assigned to these routes
        $vehicleModel   = new \App\Models\VehicleModel();
        $vehicleBuilder = $vehicleModel->where('type', $slug);
        if (!empty($routeIds)) {
            $vehicleBuilder->orWhereIn('route_id', $routeIds);
        }
        $vehicles   = $vehicleBuilder->findAll();
        $vehicleIds = array_column($vehicles, 'id');

        // 3. Delete queue records for affected routes and vehicles
        if (!empty($routeIds) || !empty($vehicleIds)) {
            $queueBuilder = $db->table('queue');
            if (!empty($routeIds)) {
                $queueBuilder->whereIn('route_id', $routeIds);
            }
            if (!empty($vehicleIds)) {
                $queueBuilder->orWhereIn('vehicle_id', $vehicleIds);
            }
            $queueBuilder->delete();
        }

        // 4. Delete departure rules for affected routes
        if (!empty($routeIds)) {
            $db->table('departure_rules')->whereIn('route_id', $routeIds)->delete();
        }

        // 5. Delete staff route assignments (user_routes) for affected routes
        if (!empty($routeIds)) {
            $db->table('user_routes')->whereIn('route_id', $routeIds)->delete();
        }

        // 6. Delete fares for affected routes
        if (!empty($routeIds)) {
            $db->table('fares')->whereIn('route_id', $routeIds)->delete();
        }

        // 7. Delete the routes themselves
        if (!empty($routeIds)) {
            $routeModel->whereIn('id', $routeIds)->delete();
        }

        // 8. Delete vehicles
        if (!empty($vehicleIds)) {
            $vehicleModel->whereIn('id', $vehicleIds)->delete();
        }

        // 9. Delete the vehicle type
        $types->delete($id);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Failed to delete vehicle type "' . $name . '".');
        }

        $this->logActivity('Delete vehicle type', 'Deleted vehicle type ' . $name . ' (' . $slug . ') and associated routes, fares, and vehicles.');
        $this->broadcastUpdate('vehicle_type_update', ['action' => 'delete', 'slug' => $slug]);

        return redirect()->back()->with('success', 'Vehicle type "' . $name . '" and all connected fares, routes, and vehicles were deleted successfully.');
    }

    public function update($id)
    {
        $types = new VehicleTypeModel();
        $type  = $types->find($id);

        if (!$type) {
            return redirect()->back()->with('error', 'Vehicle type not found.');
        }

        $name = trim((string) $this->request->getPost('name'));
        if ($name === '' || strlen($name) < 2 || strlen($name) > 80) {
            return redirect()->back()->with('error', 'Enter a vehicle type name between 2 and 80 characters.');
        }

        $oldSlug = $type['slug'];
        $newSlug = strtolower((string) preg_replace('/[^a-z0-9]+/i', '_', $name));
        $newSlug = trim($newSlug, '_');

        // Check for duplicate slug on other types
        $existing = $types->where('slug', $newSlug)->where('id !=', $id)->first();
        if ($existing) {
            return redirect()->back()->with('error', 'A vehicle type with that name/slug already exists.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        if ($oldSlug !== $newSlug) {
            $db->table('routes')->where('vehicle_type', $oldSlug)->update(['vehicle_type' => $newSlug]);
            $db->table('vehicles')->where('type', $oldSlug)->update(['type' => $newSlug]);
        }

        $types->update($id, [
            'name' => $name,
            'slug' => $newSlug,
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Failed to update vehicle type.');
        }

        $this->logActivity('Update vehicle type', 'Updated vehicle type ' . $type['name'] . ' to ' . $name . '.');
        $this->broadcastUpdate('vehicle_type_update', ['action' => 'update', 'old_slug' => $oldSlug, 'new_slug' => $newSlug]);

        return redirect()->back()->with('success', 'Vehicle type updated successfully to "' . $name . '".');
    }
}
