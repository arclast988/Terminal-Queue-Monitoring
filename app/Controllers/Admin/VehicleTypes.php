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
}
