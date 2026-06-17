<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LogModel;

class Logs extends BaseController
{
    protected $logModel;

    public function __construct()
    {
        $this->logModel = new LogModel();
    }

    public function index()
    {
        // Join with users table to get user name
        $logs = $this->logModel->select('audit_logs.*, users.username, users.full_name')
            ->join('users', 'users.id = audit_logs.user_id', 'left')
            ->orderBy('audit_logs.timestamp', 'DESC')
            ->paginate(50);

        $data = [
            'title' => 'System Logs',
            'logs'  => $logs,
            'pager' => $this->logModel->pager
        ];

        return view('admin/logs/index', $data);
    }

    public function delete($id)
    {
        $this->logModel->delete($id);
        return redirect()->to('/admin/logs')->with('success', 'Log deleted successfully.');
    }

    public function clear()
    {
        $this->logModel->emptyTable();
        return redirect()->to('/admin/logs')->with('success', 'All logs cleared successfully.');
    }
}
