<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VehicleModel;

class Vehicles extends BaseController
{
    protected $vehicleModel;

    public function __construct()
    {
        $this->vehicleModel = new VehicleModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Vehicle Register',
            'vehicles' => $this->vehicleModel->orderBy('created_at', 'DESC')->findAll()
        ];

        return view('admin/vehicles/index', $data);
    }

    public function store()
    {
        $rules = [
            'plate_number' => 'required|min_length[5]|max_length[20]|is_unique[vehicles.plate_number]',
            'driver_name'  => 'required|min_length[3]|max_length[100]',
            'type'         => 'required|in_list[jeepney,van,minibus]',
            'capacity'     => 'required|integer|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->vehicleModel->save([
            'plate_number' => $this->request->getPost('plate_number'),
            'driver_name'  => $this->request->getPost('driver_name'),
            'type'         => $this->request->getPost('type'),
            'capacity'     => $this->request->getPost('capacity'),
            'status'       => 'active',
        ]);

        $this->logActivity('Register vehicle', 'Registered vehicle ' . $this->request->getPost('plate_number') . ' (' . $this->request->getPost('type') . ') - Driver: ' . $this->request->getPost('driver_name'));

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
            'vehicle' => $vehicle
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
            'plate_number' => 'required|min_length[5]|max_length[20]|is_unique[vehicles.plate_number,id,' . $id . ']',
            'driver_name'  => 'required|min_length[3]|max_length[100]',
            'type'         => 'required|in_list[jeepney,van,minibus]',
            'capacity'     => 'required|integer|greater_than[0]',
            'status'       => 'required|in_list[active,maintenance]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->vehicleModel->update($id, [
            'plate_number' => $this->request->getPost('plate_number'),
            'driver_name'  => $this->request->getPost('driver_name'),
            'type'         => $this->request->getPost('type'),
            'capacity'     => $this->request->getPost('capacity'),
            'status'       => $this->request->getPost('status'),
        ]);

        $this->logActivity('Update vehicle', 'Updated vehicle ' . $this->request->getPost('plate_number'));

        return redirect()->to('/admin/vehicles')->with('success', 'Vehicle updated successfully.');
    }

    public function delete($id)
    {
        $vehicle = $this->vehicleModel->find($id);
        if ($this->vehicleModel->delete($id)) {
            if ($vehicle) {
                $this->logActivity('Delete vehicle', 'Deleted vehicle ' . $vehicle['plate_number'] . '.');
            }
            return redirect()->to('/admin/vehicles')->with('success', 'Vehicle deleted successfully.');
        }
        return redirect()->to('/admin/vehicles')->with('error', 'Failed to delete vehicle.');
    }
}
