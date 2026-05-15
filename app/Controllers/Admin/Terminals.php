<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TerminalModel;

class Terminals extends BaseController
{
    protected $terminalModel;

    public function __construct()
    {
        $this->terminalModel = new TerminalModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Manage Terminals',
            'terminals' => $this->terminalModel->findAll()
        ];

        return view('admin/terminals/index', $data);
    }

    public function create()
    {
        $data = ['title' => 'Add New Terminal'];
        return view('admin/terminals/create', $data);
    }

    public function store()
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[100]',
            'location' => 'required|min_length[3]|max_length[255]',
            'capacity' => 'required|integer|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->terminalModel->save([
            'name' => $this->request->getPost('name'),
            'location' => $this->request->getPost('location'),
            'capacity' => $this->request->getPost('capacity')
        ]);

        return redirect()->to('/admin/terminals')->with('success', 'Terminal added successfully.');
    }

    public function edit($id)
    {
        $terminal = $this->terminalModel->find($id);

        if (!$terminal) {
            return redirect()->to('/admin/terminals')->with('error', 'Terminal not found.');
        }

        $data = [
            'title' => 'Edit Terminal',
            'terminal' => $terminal
        ];

        return view('admin/terminals/edit', $data);
    }

    public function update($id)
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[100]',
            'location' => 'required|min_length[3]|max_length[255]',
            'capacity' => 'required|integer|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->terminalModel->update($id, [
            'name' => $this->request->getPost('name'),
            'location' => $this->request->getPost('location'),
            'capacity' => $this->request->getPost('capacity')
        ]);

        return redirect()->to('/admin/terminals')->with('success', 'Terminal updated successfully.');
    }

    public function delete($id)
    {
        if ($this->terminalModel->delete($id)) {
            return redirect()->to('/admin/terminals')->with('success', 'Terminal deleted successfully.');
        }
        return redirect()->to('/admin/terminals')->with('error', 'Failed to delete terminal.');
    }
}
