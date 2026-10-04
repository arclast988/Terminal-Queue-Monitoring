<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DepartureRuleModel;
use App\Models\TerminalModel;
use App\Models\RouteModel;
use App\Models\UserRouteModel;

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

    protected function assignedRouteIds(): ?array
    {
        return session()->get('role') === 'staff'
            ? (new UserRouteModel())->getRouteIdsForUser((int) session()->get('id')) : null;
    }

    private function canManageRule(array $rule): bool
    {
        $ids = $this->assignedRouteIds();
        if ($ids === null) return true;
        if (!empty($rule['route_id'])) {
            return in_array((int) $rule['route_id'], $ids, true);
        }

        return in_array((int) ($rule['terminal_id'] ?? 0), $this->assignedTerminalIds() ?? [], true);
    }

    protected function assignedTerminalIds(): ?array
    {
        $ids = $this->assignedRouteIds();
        if ($ids === null) return null;
        if (!$ids) return [];

        return array_values(array_unique(array_map('intval', array_column(
            $this->routeModel->select('terminal_id')->whereIn('id', $ids)->findAll(), 'terminal_id'
        ))));
    }

    private function accessibleTerminals(): array
    {
        $ids = $this->assignedTerminalIds();
        if ($ids === null) return $this->terminalModel->findAll();
        if (!$ids) return [];
        return $this->terminalModel->whereIn('id', $ids)->findAll();
    }

    protected function recalculateRuleScope(int $terminalId, ?int $routeId): void
    {
        $queue = new \App\Models\QueueModel();
        if ($routeId !== null) {
            $queue->recalculateSchedule($routeId, true);
        } else {
            foreach ((new RouteModel())->where('terminal_id', $terminalId)->findAll() as $route) {
                $queue->recalculateSchedule((int) $route['id'], true);
            }
        }
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
        $ids = $this->assignedRouteIds();
        if ($ids !== null) {
            $this->routeModel->whereIn('routes.id', $ids ?: [0]);
        }
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
        return departure_clock_value($time, $this->getPrefix() === 'staff');
    }

    /** Existing rules for form checks, tagged with their destination round scope. */
    private function existingRulesForForm(): array
    {
        $rules = $this->ruleModel
            ->select('departure_rules.id, departure_rules.terminal_id, departure_rules.route_id, departure_rules.time_from, departure_rules.time_to, departure_rules.label, departure_rules.day_of_week, departure_rules.days_of_week, departure_rules.round_number, routes.destination as route_destination')
            ->join('routes', 'routes.id = departure_rules.route_id', 'left')
            ->findAll();
        foreach ($rules as &$rule) {
            $rule['round_scope'] = departure_round_scope((int) $rule['terminal_id'], $rule['route_destination'] ?? null);
        }
        unset($rule);
        return $rules;
    }

    private function parseWaitMinutes(): ?int
    {
        $minutes = $this->request->getPost('wait_minutes');
        if ($this->getPrefix() === 'staff' && $minutes !== null) {
            return is_scalar($minutes) && preg_match('/^[1-9]\d*$/', (string) $minutes) && (int) $minutes <= 1439 ? (int) $minutes : null;
        }
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

    private function parseRuleDays(): ?array
    {
        $selected = $this->request->getPost('days_of_week');
        if ($selected !== null || $this->request->getPost('day_selection') === '1') {
            if (!is_array($selected) || !$selected) return null;
            foreach ($selected as $day) {
                if (!is_scalar($day) || !preg_match('/^[1-7]$/', (string) $day)) return null;
            }
            $days = array_values(array_unique(array_map('intval', $selected)));
            sort($days);
            return $days;
        }
        // Accept the old single-day request format while existing clients refresh.
        $day = $this->request->getPost('day_of_week');
        if ($day === null || $day === '') return range(1, 7);
        return is_scalar($day) && preg_match('/^[1-7]$/', (string) $day) ? [(int) $day] : null;
    }

    private function findDayOverlap(array $candidates, array $days): ?array
    {
        foreach ($candidates as $candidate) {
            $existingDays = departure_rule_days($candidate);
            // Preserve specific-day overrides of an every-day fallback.
            if ((count($days) === 7) !== (count($existingDays) === 7)) continue;
            if (array_intersect($days, $existingDays)) return $candidate;
        }
        return null;
    }

    public function index()
    {
        $rules = $this->ruleModel
            ->select('departure_rules.*, terminals.name as terminal_name, routes.destination as route_destination')
            ->join('terminals', 'terminals.id = departure_rules.terminal_id', 'left')
            ->join('routes', 'routes.id = departure_rules.route_id', 'left')
            ->orderBy('departure_rules.terminal_id', 'ASC')
            ->orderBy('routes.destination', 'ASC')
            ->orderBy('departure_rules.round_number', 'ASC')
            ->orderBy('departure_rules.time_from', 'ASC')
            ->findAll();

        $ids = $this->assignedRouteIds();
        if ($ids !== null) {
            $terminalIds = array_column($this->accessibleTerminals(), 'id');
            $rules = array_values(array_filter($rules, static fn(array $r): bool =>
                (!empty($r['route_id']) && in_array((int) $r['route_id'], $ids, true))
                || (empty($r['route_id']) && in_array((int) $r['terminal_id'], array_map('intval', $terminalIds), true))));
        }
        foreach ($rules as &$rule) $rule['can_manage'] = $this->canManageRule($rule);
        unset($rule);
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
            'terminals'       => $this->accessibleTerminals(),
            'routes'          => $this->getRoutesForDropdown(),
            'selectedRouteId' => $selectedRouteId,
            'returnRoute'     => $returnRoute,
            'listUrl'         => $this->listUrlForRoute($returnRoute),
            'existingRules'   => $this->existingRulesForForm(),
        ];
        return view('admin/departure-rules/create', $data);
    }

    public function store()
    {
        $waitMinutes = $this->parseWaitMinutes();
        $days = $this->parseRuleDays();
        if ($days === null) return redirect()->back()->withInput()->with('error', 'Select at least one valid day.');
        $day = count($days) === 1 ? $days[0] : null;
        $round = $this->request->getPost('round_number');

        $dataToValidate = array_merge($this->request->getPost(), [
            'wait_minutes' => $waitMinutes, 'day_of_week' => $day
        ]);

        $rules = [
            'day_of_week' => 'permit_empty|integer|greater_than_equal_to[1]|less_than_equal_to[7]',
            'round_number' => 'required|integer|greater_than[0]|less_than_equal_to[999]',
            'label' => 'permit_empty|max_length[50]',
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
            $msg = $this->getPrefix() === 'staff' ? 'Enter start and end times with AM or PM, such as 5:00 AM.' : 'Time From and Time To must use 24-hour HH:MM format.';
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

        if (!$this->canManageRule(['terminal_id' => $terminalId, 'route_id' => $routeId])) {
            return $this->response->setStatusCode(403)->setBody('Choose an assigned destination or a terminal where you dispatch.');
        }
        // Validate time_from < time_to
        if ($timeFrom >= $timeTo) {
            $msg = 'Time From must be earlier than Time To.';
            return redirect()->back()->withInput()->with('error', $msg)->with('errors', ['time' => $msg]);
        }

        // Vehicle-type routes sharing a destination use the same rule scope.
        $overlapQuery = $this->ruleModel
            ->where('time_from <', $timeTo)
            ->where('time_to >', $timeFrom);
        if ($routeId !== null) {
            $overlapQuery->where('terminal_id', $terminalId)->whereIn('route_id',
                $this->routeModel->getDestinationRouteIds($terminalId, $route['destination']));
        } else {
            $overlapQuery->where('terminal_id', $terminalId)->where('route_id', null);
        }
        $overlap = $this->findDayOverlap($overlapQuery->where('round_number', $round)->findAll(), $days);
        if ($overlap) {
            $msg = 'This time range overlaps with an existing rule: ' . operations_time($overlap['time_from']) . ' - ' . operations_time($overlap['time_to']) . ' (' . ($overlap['label'] ?? 'No label') . ').';
            return redirect()->back()->withInput()->with('error', $msg)->with('errors', ['overlap' => $msg]);
        }

        $label       = $this->request->getPost('label') ?: null;

        $this->ruleModel->save([
            'terminal_id'  => $terminalId,
            'route_id'     => $routeId,
            'time_from'    => $timeFrom,
            'time_to'      => $timeTo,
            'day_of_week' => $day === null ? null : (int) $day,
            'days_of_week' => count($days) === 7 ? null : implode(',', $days),
            'round_number' => (int) $round,
            'wait_minutes' => $waitMinutes,
            'label'        => $label
        ]);

        $this->logActivity('Create departure rule', 'Added departure rule: ' . ($label ?? 'Unlabeled') . ' (' . operations_time($timeFrom) . ' - ' . operations_time($timeTo) . ', ' . $waitMinutes . ' min).');

        $this->recalculateRuleScope($terminalId, $routeId);
        $this->broadcastUpdate('queue_update', ['action' => 'recalculate']);

        return redirect()->to($this->listUrlForRoute($this->request->getPost('return_route')))->with('success', 'Departure rule added successfully.');
    }

    public function edit($id)
    {
        $rule = $this->ruleModel->find($id);
        if ($rule && !$this->canManageRule($rule)) {
            return $this->response->setStatusCode(403)->setBody('This departure rule is outside your assigned destinations.');
        }

        if (!$rule) {
            return redirect()->to('/' . $this->getPrefix() . '/departure-rules')->with('error', 'Rule not found.');
        }

        $data = [
            'title'         => 'Edit Departure Rule',
            'rule'          => $rule,
            'prefix'        => $this->getPrefix(),
            'terminals'     => $this->accessibleTerminals(),
            'routes'        => $this->getRoutesForDropdown(),
            'existingRules' => $this->existingRulesForForm(),
            'returnRoute'   => $this->normalizeReturnRoute($this->request->getGet('return_route')),
        ];

        $data['listUrl'] = $this->listUrlForRoute($data['returnRoute']);

        return view('admin/departure-rules/edit', $data);
    }

    public function update($id)
    {
        $existingRule = $this->ruleModel->find($id);
        if (!$existingRule) return $this->response->setStatusCode(404)->setBody('Rule not found.');
        if (!$this->canManageRule($existingRule)) return $this->response->setStatusCode(403)->setBody('This departure rule is outside your assigned destinations.');
        $waitMinutes = $this->parseWaitMinutes();
        $days = $this->parseRuleDays();
        if ($days === null) return redirect()->back()->withInput()->with('error', 'Select at least one valid day.');
        $day = count($days) === 1 ? $days[0] : null;
        $round = $this->request->getPost('round_number');

        $dataToValidate = array_merge($this->request->getPost(), [
            'wait_minutes' => $waitMinutes, 'day_of_week' => $day
        ]);

        $rules = [
            'day_of_week' => 'permit_empty|integer|greater_than_equal_to[1]|less_than_equal_to[7]',
            'round_number' => 'required|integer|greater_than[0]|less_than_equal_to[999]',
            'label' => 'permit_empty|max_length[50]',
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
            $msg = $this->getPrefix() === 'staff' ? 'Enter start and end times with AM or PM, such as 5:00 AM.' : 'Time From and Time To must use 24-hour HH:MM format.';
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

        if (!$this->canManageRule(['terminal_id' => $terminalId, 'route_id' => $routeId])) {
            return $this->response->setStatusCode(403)->setBody('Choose an assigned destination or a terminal where you dispatch.');
        }
        // Validate time_from < time_to
        if ($timeFrom >= $timeTo) {
            $msg = 'Time From must be earlier than Time To.';
            return redirect()->back()->withInput()->with('error', $msg)->with('errors', ['time' => $msg]);
        }

        // Check the whole destination scope, excluding this rule.
        $overlapQuery = $this->ruleModel
            ->where('time_from <', $timeTo)
            ->where('time_to >', $timeFrom)
            ->where('id !=', $id);
        if ($routeId !== null) {
            $overlapQuery->where('terminal_id', $terminalId)->whereIn('route_id',
                $this->routeModel->getDestinationRouteIds($terminalId, $route['destination']));
        } else {
            $overlapQuery->where('terminal_id', $terminalId)->where('route_id', null);
        }
        $overlap = $this->findDayOverlap($overlapQuery->where('round_number', $round)->findAll(), $days);
        if ($overlap) {
            $msg = 'This time range overlaps with an existing rule: ' . operations_time($overlap['time_from']) . ' - ' . operations_time($overlap['time_to']) . ' (' . ($overlap['label'] ?? 'No label') . ').';
            return redirect()->back()->withInput()->with('error', $msg)->with('errors', ['overlap' => $msg]);
        }

        $oldRule     = $this->ruleModel->find($id);
        $label       = $this->request->getPost('label') ?: null;

        if ($oldRule && $this->inputsUnchanged([
            'terminal_id'  => $oldRule['terminal_id'] ?? '',
            'route_id'     => $oldRule['route_id'] ?? '',
            'time_from'    => $oldRule['time_from'] ?? '',
            'time_to'      => $oldRule['time_to'] ?? '',
            'days_of_week' => departure_rule_days($oldRule),
            'round_number' => $oldRule['round_number'] ?? '',
            'wait_minutes' => $oldRule['wait_minutes'] ?? '',
            'label'        => $oldRule['label'] ?? '',
        ], [
            'terminal_id'  => $terminalId,
            'route_id'     => $routeId ?? '',
            'time_from'    => $timeFrom,
            'time_to'      => $timeTo,
            'days_of_week' => $days,
            'round_number' => (int) $round,
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
            'day_of_week' => $day === null ? null : (int) $day,
            'days_of_week' => count($days) === 7 ? null : implode(',', $days),
            'round_number' => (int) $round,
            'wait_minutes' => $waitMinutes,
            'label'        => $label
        ]);

        $this->logActivity('Update departure rule', 'Updated departure rule: ' . ($oldRule['label'] ?? '#' . $id) . '. Before: ' . $oldRule['wait_minutes'] . ' min (' . operations_time($oldRule['time_from']) . '-' . operations_time($oldRule['time_to']) . '). After: ' . $waitMinutes . ' min (' . operations_time($timeFrom) . '-' . operations_time($timeTo) . ').');

        $oldRouteId = !empty($oldRule['route_id']) ? (int) $oldRule['route_id'] : null;
        if ((int) $oldRule['terminal_id'] !== $terminalId || $oldRouteId !== $routeId) {
            $this->recalculateRuleScope((int) $oldRule['terminal_id'], $oldRouteId);
        }
        $this->recalculateRuleScope($terminalId, $routeId);
        $this->broadcastUpdate('queue_update', ['action' => 'recalculate']);

        return redirect()->to($this->listUrlForRoute($this->request->getPost('return_route')))->with('success', 'Departure rule updated successfully.');
    }

    public function delete($id)
    {
        $rule = $this->ruleModel->find($id);
        if ($rule && !$this->canManageRule($rule)) {
            return $this->response->setStatusCode(403)->setBody('This departure rule is outside your assigned destinations.');
        }
        if (!$rule) {
            return redirect()->to('/' . $this->getPrefix() . '/departure-rules')->with('error', 'Departure rule not found.');
        }

        if ($this->ruleModel->delete($id)) {
            $ruleTime = operations_time($rule['time_from']) . ' - ' . operations_time($rule['time_to']);
            $ruleLabel = !empty($rule['label']) && $rule['label'] !== '-' ? ' (' . $rule['label'] . ')' : '';
            $this->logActivity('Delete departure rule', 'Deleted departure rule: ' . $rule['time_from'] . ' - ' . $rule['time_to'] . '.');
            $this->ruleModel->compactRounds((int) $rule['terminal_id'], !empty($rule['route_id']) ? (int) $rule['route_id'] : null);
            $this->recalculateRuleScope((int) $rule['terminal_id'], !empty($rule['route_id']) ? (int) $rule['route_id'] : null);
            $this->broadcastUpdate('queue_update', ['action' => 'recalculate']);
            return redirect()->to($this->listUrlForRoute($this->request->getPost('return_route')))->with('success', 'Departure rule "' . $ruleTime . $ruleLabel . '" deleted successfully.');
        }
        return redirect()->to($this->listUrlForRoute($this->request->getPost('return_route')))->with('error', 'Failed to delete rule.');
    }
}
