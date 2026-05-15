<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DepartureRuleModel;

class DepartureRules extends BaseController
{
    protected $ruleModel;

    public function __construct()
    {
        $this->ruleModel = new DepartureRuleModel();
    }

    /**
     * Get the correct URL prefix based on user role (admin or staff).
     */
    private function getPrefix(): string
    {
        $role = session()->get('role');
        return ($role === 'staff') ? 'staff' : 'admin';
    }

    public function index()
    {
        $data = [
            'title' => 'Departure Rules',
            'rules' => $this->ruleModel->orderBy('time_from', 'ASC')->findAll(),
            'prefix' => $this->getPrefix()
        ];

        return view('admin/departure-rules/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Add Departure Rule',
            'prefix' => $this->getPrefix()
        ];
        return view('admin/departure-rules/create', $data);
    }

    public function store()
    {
        // Staff cannot create rules
        if (session()->get('role') === 'staff') {
            return redirect()->to('/staff/departure-rules')->with('error', 'You do not have permission to create departure rules.');
        }

        $rules = [
            'time_from' => 'required',
            'time_to' => 'required',
            'wait_minutes' => 'required|integer|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $timeFrom = $this->request->getPost('time_from');
        $timeTo = $this->request->getPost('time_to');
        // Ensure HH:MM:SS format (HTML time input sends HH:MM)
        if (strlen($timeFrom) === 5)
            $timeFrom .= ':00';
        if (strlen($timeTo) === 5)
            $timeTo .= ':00';

        $this->ruleModel->save([
            'time_from' => $timeFrom,
            'time_to' => $timeTo,
            'wait_minutes' => $this->request->getPost('wait_minutes'),
            'label' => $this->request->getPost('label') ?: null
        ]);

        $this->logActivity('Create departure rule', 'Added departure rule: ' . $this->request->getPost('time_from') . ' - ' . $this->request->getPost('time_to') . ' (' . $this->request->getPost('wait_minutes') . ' min).');

        return redirect()->to('/' . $this->getPrefix() . '/departure-rules')->with('success', 'Departure rule added successfully.');
    }

    public function edit($id)
    {
        // Staff cannot edit rules
        if (session()->get('role') === 'staff') {
            return redirect()->to('/staff/departure-rules')->with('error', 'You do not have permission to edit departure rules.');
        }

        $rule = $this->ruleModel->find($id);

        if (!$rule) {
            return redirect()->to('/' . $this->getPrefix() . '/departure-rules')->with('error', 'Rule not found.');
        }

        $data = [
            'title' => 'Edit Departure Rule',
            'rule' => $rule,
            'prefix' => $this->getPrefix()
        ];

        return view('admin/departure-rules/edit', $data);
    }

    public function update($id)
    {
        // Staff cannot update rules
        if (session()->get('role') === 'staff') {
            return redirect()->to('/staff/departure-rules')->with('error', 'You do not have permission to edit departure rules.');
        }

        $rules = [
            'time_from' => 'required',
            'time_to' => 'required',
            'wait_minutes' => 'required|integer|greater_than[0]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $timeFrom = $this->request->getPost('time_from');
        $timeTo = $this->request->getPost('time_to');
        if (strlen($timeFrom) === 5)
            $timeFrom .= ':00';
        if (strlen($timeTo) === 5)
            $timeTo .= ':00';

        $this->ruleModel->update($id, [
            'time_from' => $timeFrom,
            'time_to' => $timeTo,
            'wait_minutes' => $this->request->getPost('wait_minutes'),
            'label' => $this->request->getPost('label') ?: null
        ]);

        $this->logActivity('Update departure rule', 'Updated departure rule #' . $id . '.');

        return redirect()->to('/' . $this->getPrefix() . '/departure-rules')->with('success', 'Departure rule updated successfully.');
    }

    public function delete($id)
    {
        // Staff cannot delete rules
        if (session()->get('role') === 'staff') {
            return redirect()->to('/staff/departure-rules')->with('error', 'You do not have permission to delete departure rules.');
        }

        $rule = $this->ruleModel->find($id);
        if ($this->ruleModel->delete($id)) {
            if ($rule) {
                $this->logActivity('Delete departure rule', 'Deleted departure rule: ' . $rule['time_from'] . ' - ' . $rule['time_to'] . '.');
            }
            return redirect()->to('/' . $this->getPrefix() . '/departure-rules')->with('success', 'Departure rule deleted successfully.');
        }
        return redirect()->to('/' . $this->getPrefix() . '/departure-rules')->with('error', 'Failed to delete rule.');
    }
}

