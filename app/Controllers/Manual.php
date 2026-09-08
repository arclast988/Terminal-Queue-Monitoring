<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Manual extends BaseController
{
    /**
     * Public Commuter & Passenger User Guide
     * Accessible at /manual and /user-manual (No login required)
     */
    public function index()
    {
        $data = [
            'title'              => 'Commuter User Guide & Error Recovery',
            'breadcrumb_current' => 'User Guide',
            'role'               => 'guest',
        ];

        return view('manual/commuter', $data);
    }

    /**
     * Administrator & Super Administrator User Manual
     * Accessible at /admin/manual (Requires auth:admin)
     */
    public function admin()
    {
        $data = [
            'title' => 'Administrator User Manual & Error Recovery Guide',
            'role'  => session()->get('role') ?? 'admin',
        ];

        return view('manual/admin', $data);
    }

    /**
     * Dispatcher (Staff) User Manual
     * Accessible at /staff/manual (Requires auth:staff)
     */
    public function staff()
    {
        $data = [
            'title' => 'Dispatcher User Manual & Error Recovery Guide',
            'role'  => 'staff',
        ];

        return view('manual/staff', $data);
    }
}
