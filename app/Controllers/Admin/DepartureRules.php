<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DepartureRuleModel;
use App\Models\TerminalModel;
use App\Models\RouteModel;

class DepartureRules extends BaseController
{
    protected $ruleModel;
    protected $terminalModel;
    protected $routeModel;

    public function __construct()
    {
        $this->ruleModel     = new DepartureRuleModel();
        $this->terminalModel = new TerminalModel();
        $this->routeModel    = new RouteModel();
    }

    /**
     * Get the correct URL prefix based on user role (admin or staff).
     */
    private function getPrefix(): string
    {
        $role = session()->get('role');
        return ($role === 'staff') ? 'staff' : 'admin';
    }

    /**
     * Routes for the destination dropdown, ordered by destination then vehicle type.
     */
    private function getRoutesForDropdown(): array
    {
        return $this->routeModel
            ->select('routes.id, routes.destination, routes.vehicle_type, routes.terminal_id')
            ->orderBy('routes.destination', 'ASC')
            ->orderBy('routes.vehicle_type', 'ASC')
            ->findAll();
    }

    public function index()
    {
        $rules = $this->ruleModel
            ->select('departure_rules.*, terminals.name as terminal_name, routes.destination as route_destination')
            ->join('terminals', 'terminals.id = departure_rules.terminal_id', 'left')
            ->join('routes', 'routes.id = departure_rules.route_id', 'left')
            ->orderBy('departure_rules.terminal_id', 'ASC')
            ->orderBy('departure_rules.time_from', 'ASC')
            ->findAll();

        $data = [
            'title'  => 'Departure Rules',
            'rules'  => $rules,
            'prefix' => $this->getPrefix()
        ];

        return view('admin/departure-rules/index', $data);
    }

    public function create()
    {
        $data = [
            'title'     => 'Add Departure Rule',
            'prefix'    => $this->getPrefix(),
            'terminals' => $this->terminalModel->findAll(),
            'routes'    => $this->getRoutesForDropdown(),
        ];
        return view('admin/departure-rules/create', $data);
    }

    public function store()
    {
        // Staff cannot create rules
        if (session()->get('role') === 'staff') {
            return redirect()->to('/staff/departure-rules')->with('error', 'You do not have permission to create departure rules.');
        }

        // Convert wait_hours and wait_mins to wait_minutes (integer)
        $hours = (int)$this->request->getPost('wait_hours');
        $mins = (int)$this->request->getPost('wait_mins');
        $waitMinutes = ($hours * 60) + $mins;

        $dataToValidate = array_merge($this->request->getPost(), [
            'wait_minutes' => $waitMinutes
        ]);

        $rules = [
            'time_from'    => 'required',
            'time_to'      => 'required',
            'wait_minutes' => 'required|integer|greater_than[0]',
            'terminal_id'  => 'required|integer|is_not_unique[terminals.id]',
            'route_id'     => 'permit_empty|integer|is_not_unique[routes.id]',
        ];

        if (!$this->validateData($dataToValidate, $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $terminalId = (int)$this->request->getPost('terminal_id');
        $timeFrom   = $this->request->getPost('time_from');
        $timeTo     = $this->request->getPost('time_to');

        // Resolve the optional destination/route. A route-specific rule inherits its
        // route's terminal, so terminal_id can never disagree with the route.
        $routeId = $this->request->getPost('route_id');
        $routeId = ($routeId !== null && $routeId !== '') ? (int) $routeId : null;
        if ($routeId !== null) {
            $route = $this->routeModel->find($routeId);
            if ($route) {
                $terminalId = (int) $route['terminal_id'];
            }
        }

        // Ensure HH:MM:SS format (HTML time input sends HH:MM)
        if (strlen($timeFrom) === 5) $timeFrom .= ':00';
        if (strlen($timeTo) === 5)   $timeTo   .= ':00';

        // Validate time_from < time_to
        if ($timeFrom >= $timeTo) {
            return redirect()->back()->withInput()->with('error', 'Time From must be earlier than Time To.');
        }

        // Check for an overlapping rule in the same scope (same route, or terminal-wide default).
        $overlapQuery = $this->ruleModel
            ->where('time_from <', $timeTo)
            ->where('time_to >', $timeFrom);
        if ($routeId !== null) {
            $overlapQuery->where('route_id', $routeId);
        } else {
            $overlapQuery->where('terminal_id', $terminalId)->where('route_id', null);
        }
        $overlap = $overlapQuery->first();
        if ($overlap) {
            return redirect()->back()->withInput()->with('error', 'This time range overlaps with an existing rule: ' . date('g:i A', strtotime($overlap['time_from'])) . ' – ' . date('g:i A', strtotime($overlap['time_to'])) . ' (' . ($overlap['label'] ?? 'No label') . ').');
        }

        $label       = $this->request->getPost('label') ?: null;

        $this->ruleModel->save([
            'terminal_id'  => $terminalId,
            'route_id'     => $routeId,
            'time_from'    => $timeFrom,
            'time_to'      => $timeTo,
            'wait_minutes' => $waitMinutes,
            'label'        => $label
        ]);

        $this->logActivity('Create departure rule', 'Added departure rule: ' . ($label ?? 'Unlabeled') . ' (' . date('g:i A', strtotime($timeFrom)) . ' – ' . date('g:i A', strtotime($timeTo)) . ', ' . $waitMinutes . ' min).');

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
            'title'     => 'Edit Departure Rule',
            'rule'      => $rule,
            'prefix'    => $this->getPrefix(),
            'terminals' => $this->terminalModel->findAll(),
            'routes'    => $this->getRoutesForDropdown(),
        ];

        return view('admin/departure-rules/edit', $data);
    }

    public function update($id)
    {
        // Staff cannot update rules
        if (session()->get('role') === 'staff') {
            return redirect()->to('/staff/departure-rules')->with('error', 'You do not have permission to edit departure rules.');
        }

        // Convert wait_hours and wait_mins to wait_minutes (integer)
        $hours = (int)$this->request->getPost('wait_hours');
        $mins = (int)$this->request->getPost('wait_mins');
        $waitMinutes = ($hours * 60) + $mins;

        $dataToValidate = array_merge($this->request->getPost(), [
            'wait_minutes' => $waitMinutes
        ]);

        $rules = [
            'time_from'    => 'required',
            'time_to'      => 'required',
            'wait_minutes' => 'required|integer|greater_than[0]',
            'terminal_id'  => 'required|integer|is_not_unique[terminals.id]',
            'route_id'     => 'permit_empty|integer|is_not_unique[routes.id]',
        ];

        if (!$this->validateData($dataToValidate, $rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $terminalId = (int)$this->request->getPost('terminal_id');
        $timeFrom   = $this->request->getPost('time_from');
        $timeTo     = $this->request->getPost('time_to');

        // Resolve the optional destination/route. A route-specific rule inherits its
        // route's terminal, so terminal_id can never disagree with the route.
        $routeId = $this->request->getPost('route_id');
        $routeId = ($routeId !== null && $routeId !== '') ? (int) $routeId : null;
        if ($routeId !== null) {
            $route = $this->routeModel->find($routeId);
            if ($route) {
                $terminalId = (int) $route['terminal_id'];
            }
        }

        if (strlen($timeFrom) === 5) $timeFrom .= ':00';
        if (strlen($timeTo) === 5)   $timeTo   .= ':00';

        // Validate time_from < time_to
        if ($timeFrom >= $timeTo) {
            return redirect()->back()->withInput()->with('error', 'Time From must be earlier than Time To.');
        }

        // Check for an overlapping rule in the same scope (same route, or terminal-wide default), excluding this rule.
        $overlapQuery = $this->ruleModel
            ->where('time_from <', $timeTo)
            ->where('time_to >', $timeFrom)
            ->where('id !=', $id);
        if ($routeId !== null) {
            $overlapQuery->where('route_id', $routeId);
        } else {
            $overlapQuery->where('terminal_id', $terminalId)->where('route_id', null);
        }
        $overlap = $overlapQuery->first();
        if ($overlap) {
            return redirect()->back()->withInput()->with('error', 'This time range overlaps with an existing rule: ' . date('g:i A', strtotime($overlap['time_from'])) . ' – ' . date('g:i A', strtotime($overlap['time_to'])) . ' (' . ($overlap['label'] ?? 'No label') . ').');
        }

        $oldRule     = $this->ruleModel->find($id);
        $label       = $this->request->getPost('label') ?: null;

        $this->ruleModel->update($id, [
            'terminal_id'  => $terminalId,
            'route_id'     => $routeId,
            'time_from'    => $timeFrom,
            'time_to'      => $timeTo,
            'wait_minutes' => $waitMinutes,
            'label'        => $label
        ]);

        $this->logActivity('Update departure rule', 'Updated departure rule: ' . ($oldRule['label'] ?? '#' . $id) . '. Before: ' . $oldRule['wait_minutes'] . ' min (' . date('g:i A', strtotime($oldRule['time_from'])) . '–' . date('g:i A', strtotime($oldRule['time_to'])) . '). After: ' . $waitMinutes . ' min (' . date('g:i A', strtotime($timeFrom)) . '–' . date('g:i A', strtotime($timeTo)) . ').');

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
