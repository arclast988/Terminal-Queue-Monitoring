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
        $rules = $this->terminalValidationRules();

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->terminalModel->save([
            'name' => trim((string) $this->request->getPost('name')),
            'location' => trim((string) $this->request->getPost('location')),
            'capacity' => (int) $this->request->getPost('capacity')
        ]);

        $this->logActivity('Create terminal', 'Added terminal: ' . $this->request->getPost('name'));
        $this->broadcastUpdate('fare_update', ['action' => 'terminal_created']);

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
        $rules = $this->terminalValidationRules();

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $terminal = $this->terminalModel->find($id);
        if (!$terminal) {
            return redirect()->to('/admin/terminals')->with('error', 'Terminal not found.');
        }

        $newData = [
            'name'     => trim((string) $this->request->getPost('name')),
            'location' => trim((string) $this->request->getPost('location')),
            'capacity' => (int) $this->request->getPost('capacity'),
        ];

        if ($this->inputsUnchanged([
            'name'     => trim((string) ($terminal['name'] ?? '')),
            'location' => trim((string) ($terminal['location'] ?? '')),
            'capacity' => $terminal['capacity'] ?? '',
        ], $newData)) {
            return $this->noChangesResponse();
        }

        $this->terminalModel->update($id, $newData);

        $this->logActivity('Update terminal', 'Updated terminal: ' . $this->request->getPost('name'));
        $this->broadcastUpdate('fare_update', ['action' => 'terminal_updated', 'id' => (int) $id]);

        return redirect()->to('/admin/terminals')->with('success', 'Terminal updated successfully.');
    }

    public function delete($id)
    {
        $terminal = $this->terminalModel->find($id);
        if (!$terminal) {
            return redirect()->to('/admin/terminals')->with('error', 'Terminal not found.');
        }

        if ($this->terminalModel->delete($id)) {
            $this->logActivity('Delete terminal', 'Deleted terminal: ' . $terminal['name']);
            $this->broadcastUpdate('fare_update', ['action' => 'terminal_deleted', 'id' => (int) $id]);
            return redirect()->to('/admin/terminals')->with('success', 'Terminal "' . $terminal['name'] . '" deleted successfully.');
        }
        return redirect()->to('/admin/terminals')->with('error', 'Failed to delete terminal.');
    }

    private function terminalValidationRules(): array
    {
        return [
            'name' => [
                'rules' => 'required|min_length[3]|max_length[100]',
                'errors' => [
                    'required' => 'Please enter the terminal name.',
                    'min_length' => 'Terminal name must be at least 3 characters.',
                    'max_length' => 'Terminal name cannot exceed 100 characters.',
                ],
            ],
            'location' => [
                'rules' => 'required|min_length[3]|max_length[255]',
                'errors' => [
                    'required' => 'Please enter the terminal location.',
                    'min_length' => 'Terminal location must be at least 3 characters.',
                    'max_length' => 'Terminal location cannot exceed 255 characters.',
                ],
            ],
            'capacity' => [
                'rules' => 'required|integer|greater_than[0]',
                'errors' => [
                    'required' => 'Please enter the maximum number of vehicles.',
                    'integer' => 'Terminal capacity must be a whole number.',
                    'greater_than' => 'Terminal capacity must be at least 1 vehicle.',
                ],
            ],
        ];
    }

    public function bulkAction()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin'], true)) {
            return redirect()->to('/admin/terminals')->with('error', 'You do not have permission to delete terminals.');
        }

        $rawIds = $this->request->getPost('ids');
        if (is_string($rawIds)) {
            $ids = array_filter(array_map('intval', explode(',', $rawIds)));
        } elseif (is_array($rawIds)) {
            $ids = array_filter(array_map('intval', $rawIds));
        } else {
            $ids = [];
        }

        if (empty($ids)) {
            return redirect()->to('/admin/terminals')->with('error', 'No terminals selected for deletion.');
        }

        $terminals = $this->terminalModel->whereIn('id', $ids)->findAll();
        if (empty($terminals)) {
            return redirect()->to('/admin/terminals')->with('error', 'Selected terminals not found.');
        }

        $deletedCount = 0;
        $failedNames = [];

        foreach ($terminals as $term) {
            try {
                if ($this->terminalModel->delete($term['id'])) {
                    $deletedCount++;
                } else {
                    $failedNames[] = $term['name'];
                }
            } catch (\Throwable $e) {
                $failedNames[] = $term['name'];
            }
        }

        if ($deletedCount > 0) {
            $this->logActivity('Bulk delete terminals', "Deleted {$deletedCount} terminal(s).");
            $this->broadcastUpdate('fare_update', ['action' => 'terminals_bulk_deleted']);
        }

        if ($deletedCount > 0 && empty($failedNames)) {
            return redirect()->to('/admin/terminals')->with('success', "{$deletedCount} terminal(s) deleted successfully.");
        } elseif ($deletedCount > 0 && !empty($failedNames)) {
            return redirect()->to('/admin/terminals')->with('warning', "{$deletedCount} terminal(s) deleted, but " . implode(', ', $failedNames) . " could not be deleted because they are linked to active records.");
        } else {
            return redirect()->to('/admin/terminals')->with('error', 'Could not delete selected terminal(s) because they are linked to active records (routes, fares, or rules).');
        }
    }
}

