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

    private function normalizeReturnRoute($route): string
    {
        $route = is_string($route) ? strtolower(trim($route)) : '';
        return $route !== '' && strlen($route) <= 100 && preg_match('/^[\p{L}\p{N} _.-]+$/u', $route)
            ? $route
            : 'all';
    }

    private function listUrlForRoute($route): string
    {
        $path = '/' . $this->getPrefix() . '/departure-rules';
        $route = $this->normalizeReturnRoute($route);
        return $route === 'all' ? $path : $path . '?route=' . rawurlencode($route);
    }

    /**
     * Unique destinations for the departure-rule dropdown.
     * Returns one entry per destination (with a representative route_id),
     * so the admin sees "ORMOC" instead of "ORMOC (Van)" / "ORMOC (Minibus)".
     */
    private function getRoutesForDropdown(): array
    {
        $routes = $this->routeModel
            ->select('MIN(routes.id) as id, routes.destination, routes.terminal_id')
            ->where('routes.status', 'active')
            ->groupBy('routes.destination, routes.terminal_id')
            ->orderBy('routes.destination', 'ASC')
            ->findAll();

        return $routes;
    }

    private function normalizeClockTime(?string $time): ?string
    {
        $time = trim((string) $time);
        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $time)) {
            return null;
        }

        return $time . ':00';
    }

    private function parseWaitMinutes(): ?int
    {
        $duration = trim((string) $this->request->getPost('wait_duration'));
        if ($duration !== '') {
            if (!preg_match('/^(?:[01]?\d|2[0-3]):[0-5]\d$/', $duration)) {
                return null;
            }

            [$hours, $mins] = array_map('intval', explode(':', $duration));
            $waitMinutes = ($hours * 60) + $mins;

            return $waitMinutes > 0 ? $waitMinutes : null;
        }

        $hours = $this->request->getPost('wait_hours');
        $mins = $this->request->getPost('wait_mins');
        if ($hours === null || $mins === null || $hours === '' || $mins === '') {
            return null;
        }

        $hours = (int) $hours;
        $mins = (int) $mins;
        if ($hours < 0 || $hours > 23 || $mins < 0 || $mins > 59) {
            return null;
        }

        $waitMinutes = ($hours * 60) + $mins;

        return $waitMinutes > 0 ? $waitMinutes : null;
    }

    public function index()
    {
        $rules = $this->ruleModel
            ->select('departure_rules.*, terminals.name as terminal_name, routes.destination as route_destination')
            ->join('terminals', 'terminals.id = departure_rules.terminal_id', 'left')
            ->join('routes', 'routes.id = departure_rules.route_id', 'left')
            ->orderBy('departure_rules.terminal_id', 'ASC')
            ->orderBy('routes.destination', 'ASC')
            ->orderBy('departure_rules.time_from', 'ASC')
            ->findAll();

        $routes = $this->getRoutesForDropdown();

        // Extract list of all unique route destinations available (from routes table and departure_rules)
        $destinationsMap = [];
        foreach ($routes as $r) {
            if (!empty($r['destination'])) {
                $dest = strtoupper(trim($r['destination']));
                if (!isset($destinationsMap[$dest])) {
                    $destinationsMap[$dest] = $r['id'];
                }
            }
        }
        foreach ($rules as $rule) {
            if (!empty($rule['route_destination'])) {
                $dest = strtoupper(trim($rule['route_destination']));
                if (!isset($destinationsMap[$dest])) {
                    $destinationsMap[$dest] = $rule['route_id'] ?? null;
                }
            }
        }
        ksort($destinationsMap);

        $data = [
            'title'           => 'Departure Rules',
            'rules'           => $rules,
            'routes'          => $routes,
            'destinationsMap' => $destinationsMap,
            'prefix'          => $this->getPrefix()
        ];

        return view('admin/departure-rules/index', $data);
    }

    public function create()
    {
        $selectedRouteId = $this->request->getGet('route_id');
        $returnRoute = $this->normalizeReturnRoute($this->request->getGet('return_route'));

        $data = [
            'title'           => 'Add Departure Rule',
            'prefix'          => $this->getPrefix(),
            'terminals'       => $this->terminalModel->findAll(),
            'routes'          => $this->getRoutesForDropdown(),
            'selectedRouteId' => $selectedRouteId,
            'returnRoute'     => $returnRoute,
            'listUrl'         => $this->listUrlForRoute($returnRoute),
            'existingRules'   => $this->ruleModel->select('id, terminal_id, route_id, time_from, time_to, label')->findAll(),
        ];
        return view('admin/departure-rules/create', $data);
    }

    public function store()
    {
        // Staff cannot create rules
        if (session()->get('role') === 'staff') {
            return redirect()->to('/staff/departure-rules')->with('error', 'You do not have permission to create departure rules.');
        }

        $waitMinutes = $this->parseWaitMinutes();

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
        $timeFrom   = $this->normalizeClockTime($this->request->getPost('time_from'));
        $timeTo     = $this->normalizeClockTime($this->request->getPost('time_to'));

        if ($timeFrom === null || $timeTo === null) {
            $msg = 'Time From and Time To must use 24-hour HH:MM format.';
            return redirect()->back()->withInput()->with('error', $msg)->with('errors', [
                'time' => $msg
            ]);
        }

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

        // Validate time_from < time_to
        if ($timeFrom >= $timeTo) {
            $msg = 'Time From must be earlier than Time To.';
            return redirect()->back()->withInput()->with('error', $msg)->with('errors', ['time' => $msg]);
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
            $msg = 'This time range overlaps with an existing rule: ' . date('H:i', strtotime($overlap['time_from'])) . ' - ' . date('H:i', strtotime($overlap['time_to'])) . ' (' . ($overlap['label'] ?? 'No label') . ').';
            return redirect()->back()->withInput()->with('error', $msg)->with('errors', ['overlap' => $msg]);
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

        $this->logActivity('Create departure rule', 'Added departure rule: ' . ($label ?? 'Unlabeled') . ' (' . date('H:i', strtotime($timeFrom)) . ' - ' . date('H:i', strtotime($timeTo)) . ', ' . $waitMinutes . ' min).');

        (new \App\Models\QueueModel())->recalculateSchedule();
        $this->broadcastUpdate('queue_update', ['action' => 'recalculate']);

        return redirect()->to($this->listUrlForRoute($this->request->getPost('return_route')))->with('success', 'Departure rule added successfully.');
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
            'title'         => 'Edit Departure Rule',
            'rule'          => $rule,
            'prefix'        => $this->getPrefix(),
            'terminals'     => $this->terminalModel->findAll(),
            'routes'        => $this->getRoutesForDropdown(),
            'existingRules' => $this->ruleModel->select('id, terminal_id, route_id, time_from, time_to, label')->findAll(),
            'returnRoute'   => $this->normalizeReturnRoute($this->request->getGet('return_route')),
        ];

        $data['listUrl'] = $this->listUrlForRoute($data['returnRoute']);

        return view('admin/departure-rules/edit', $data);
    }

    public function update($id)
    {
        // Staff cannot update rules
        if (session()->get('role') === 'staff') {
            return redirect()->to('/staff/departure-rules')->with('error', 'You do not have permission to edit departure rules.');
        }

        $waitMinutes = $this->parseWaitMinutes();

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
        $timeFrom   = $this->normalizeClockTime($this->request->getPost('time_from'));
        $timeTo     = $this->normalizeClockTime($this->request->getPost('time_to'));

        if ($timeFrom === null || $timeTo === null) {
            $msg = 'Time From and Time To must use 24-hour HH:MM format.';
            return redirect()->back()->withInput()->with('error', $msg)->with('errors', [
                'time' => $msg
            ]);
        }

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

        // Validate time_from < time_to
        if ($timeFrom >= $timeTo) {
            $msg = 'Time From must be earlier than Time To.';
            return redirect()->back()->withInput()->with('error', $msg)->with('errors', ['time' => $msg]);
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
            $msg = 'This time range overlaps with an existing rule: ' . date('H:i', strtotime($overlap['time_from'])) . ' - ' . date('H:i', strtotime($overlap['time_to'])) . ' (' . ($overlap['label'] ?? 'No label') . ').';
            return redirect()->back()->withInput()->with('error', $msg)->with('errors', ['overlap' => $msg]);
        }

        $oldRule     = $this->ruleModel->find($id);
        $label       = $this->request->getPost('label') ?: null;

        if ($oldRule && $this->inputsUnchanged([
            'terminal_id'  => $oldRule['terminal_id'] ?? '',
            'route_id'     => $oldRule['route_id'] ?? '',
            'time_from'    => $oldRule['time_from'] ?? '',
            'time_to'      => $oldRule['time_to'] ?? '',
            'wait_minutes' => $oldRule['wait_minutes'] ?? '',
            'label'        => $oldRule['label'] ?? '',
        ], [
            'terminal_id'  => $terminalId,
            'route_id'     => $routeId ?? '',
            'time_from'    => $timeFrom,
            'time_to'      => $timeTo,
            'wait_minutes' => $waitMinutes,
            'label'        => $label ?? '',
        ])) {
            return $this->noChangesResponse();
        }

        $this->ruleModel->update($id, [
            'terminal_id'  => $terminalId,
            'route_id'     => $routeId,
            'time_from'    => $timeFrom,
            'time_to'      => $timeTo,
            'wait_minutes' => $waitMinutes,
            'label'        => $label
        ]);

        $this->logActivity('Update departure rule', 'Updated departure rule: ' . ($oldRule['label'] ?? '#' . $id) . '. Before: ' . $oldRule['wait_minutes'] . ' min (' . date('H:i', strtotime($oldRule['time_from'])) . '-' . date('H:i', strtotime($oldRule['time_to'])) . '). After: ' . $waitMinutes . ' min (' . date('H:i', strtotime($timeFrom)) . '-' . date('H:i', strtotime($timeTo)) . ').');

        (new \App\Models\QueueModel())->recalculateSchedule();
        $this->broadcastUpdate('queue_update', ['action' => 'recalculate']);

        return redirect()->to($this->listUrlForRoute($this->request->getPost('return_route')))->with('success', 'Departure rule updated successfully.');
    }

    public function delete($id)
    {
        // Staff cannot delete rules
        if (session()->get('role') === 'staff') {
            return redirect()->to('/staff/departure-rules')->with('error', 'You do not have permission to delete departure rules.');
        }

        $rule = $this->ruleModel->find($id);
        if (!$rule) {
            return redirect()->to('/' . $this->getPrefix() . '/departure-rules')->with('error', 'Departure rule not found.');
        }

        if ($this->ruleModel->delete($id)) {
            $ruleTime = date('H:i', strtotime($rule['time_from'])) . ' - ' . date('H:i', strtotime($rule['time_to']));
            $ruleLabel = !empty($rule['label']) && $rule['label'] !== '-' ? ' (' . $rule['label'] . ')' : '';
            $this->logActivity('Delete departure rule', 'Deleted departure rule: ' . $rule['time_from'] . ' - ' . $rule['time_to'] . '.');
            (new \App\Models\QueueModel())->recalculateSchedule();
            $this->broadcastUpdate('queue_update', ['action' => 'recalculate']);
            return redirect()->to($this->listUrlForRoute($this->request->getPost('return_route')))->with('success', 'Departure rule "' . $ruleTime . $ruleLabel . '" deleted successfully.');
        }
        return redirect()->to($this->listUrlForRoute($this->request->getPost('return_route')))->with('error', 'Failed to delete rule.');
    }
}
