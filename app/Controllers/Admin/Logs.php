<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LogModel;
use App\Models\UserModel;

class Logs extends BaseController
{
    protected $logModel;

    public function __construct()
    {
        $this->logModel = new LogModel();
    }

    public function index()
    {
        // Automatically enforce 60-day retention policy
        $this->logModel->purgeOldLogs(60);

        $search       = trim((string) $this->request->getGet('q'));
        $fromDate     = trim((string) $this->request->getGet('from_date'));
        $toDate       = trim((string) $this->request->getGet('to_date'));
        $actionFilter = trim((string) $this->request->getGet('action_type'));
        $userFilter   = trim((string) $this->request->getGet('user_id'));

        $todayStr = date('Y-m-d');
        $weekStr  = date('Y-m-d', strtotime('-7 days'));

        // --- Stats (last 60 days) ---
        $db = \Config\Database::connect();
        $stats = $db->table('audit_logs')
            ->select("
                COUNT(*) as total,
                SUM(CASE WHEN timestamp >= '{$todayStr} 00:00:00' THEN 1 ELSE 0 END) as today,
                SUM(CASE WHEN timestamp >= '{$weekStr} 00:00:00' THEN 1 ELSE 0 END) as this_week,
                COUNT(DISTINCT user_id) as active_users
            ")
            ->get()
            ->getRow();

        // Distinct actions and users for filter dropdowns
        $actions = $this->logModel->select('action')->distinct()->orderBy('action', 'ASC')->findAll();
        $users   = (new UserModel())->select('users.id, users.username, users.full_name, users.role')
            ->join('audit_logs', 'audit_logs.user_id = users.id')
            ->distinct()
            ->orderBy('users.full_name', 'ASC')
            ->findAll();

        $builder = $this->_getFilteredBuilder($search, $fromDate, $toDate, $actionFilter, $userFilter);
        $logs    = $builder->orderBy('audit_logs.timestamp', 'DESC')->paginate(25);

        $data = [
            'title'        => 'System Activity Logs',
            'logs'         => $logs,
            'pager'        => $this->logModel->pager,
            'stats'        => [
                'total'        => (int) ($stats->total ?? 0),
                'today'        => (int) ($stats->today ?? 0),
                'this_week'    => (int) ($stats->this_week ?? 0),
                'active_users' => (int) ($stats->active_users ?? 0),
            ],
            'actions'      => $actions,
            'users'        => $users,
            'search'       => $search,
            'from_date'    => $fromDate,
            'to_date'      => $toDate,
            'action_type'  => $actionFilter,
            'user_id'      => $userFilter,
        ];

        return view('admin/logs/index', $data);
    }

    public function print()
    {
        // Enforce retention policy
        $this->logModel->purgeOldLogs(60);

        $search       = trim((string) $this->request->getGet('q'));
        $fromDate     = trim((string) $this->request->getGet('from_date'));
        $toDate       = trim((string) $this->request->getGet('to_date'));
        $actionFilter = trim((string) $this->request->getGet('action_type'));
        $userFilter   = trim((string) $this->request->getGet('user_id'));

        $builder = $this->_getFilteredBuilder($search, $fromDate, $toDate, $actionFilter, $userFilter);
        $results = $builder->orderBy('audit_logs.timestamp', 'DESC')->findAll();

        $selectedUser = null;
        if (!empty($userFilter)) {
            $selectedUser = (new UserModel())->find($userFilter);
        }

        $data = [
            'title'        => 'System Activity Logs Report',
            'results'      => $results,
            'search'       => $search,
            'from_date'    => $fromDate,
            'to_date'      => $toDate,
            'action_type'  => $actionFilter,
            'user_id'      => $userFilter,
            'selected_user'=> $selectedUser,
            'generated_at' => date('Y-m-d H:i:s'),
            'is_print'     => true
        ];

        return view('admin/logs/print_logs', $data);
    }



    private function _getFilteredBuilder(string $search, string $fromDate, string $toDate, string $actionFilter, string $userFilter)
    {
        $builder = $this->logModel
            ->select('audit_logs.*, users.username, users.full_name, users.role, users.email')
            ->join('users', 'users.id = audit_logs.user_id', 'left');

        if ($search !== '') {
            $builder->groupStart()
                ->like('audit_logs.action', $search)
                ->orLike('audit_logs.details', $search)
                ->orLike('users.username', $search)
                ->orLike('users.full_name', $search)
                ->orLike('users.email', $search)
                ->groupEnd();
        }

        if ($fromDate !== '') {
            $builder->where('audit_logs.timestamp >=', $fromDate . ' 00:00:00');
        }

        if ($toDate !== '') {
            $builder->where('audit_logs.timestamp <=', $toDate . ' 23:59:59');
        }

        if ($actionFilter !== '') {
            $builder->where('audit_logs.action', $actionFilter);
        }

        if ($userFilter !== '') {
            $builder->where('audit_logs.user_id', $userFilter);
        }

        return $builder;
    }
}
