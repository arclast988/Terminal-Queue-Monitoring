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
        $logs = $this->logModel->select('logs.*, users.username, users.full_name')
            ->join('users', 'users.id = logs.user_id', 'left')
            ->orderBy('logs.timestamp', 'DESC')
            ->findAll();

        $data = [
            'title' => 'System Logs',
            'logs' => $logs
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
