<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Manual extends BaseController
{
    /**
     * Public Commuter & Passenger User Guide (Redirect to Unified Help Guide)
     * Accessible at /manual and /user-manual (No login required)
     */
    public function index()
    {
        return redirect()->to(base_url('?help=1'));
    }

    /**
     * Administrator & Super Administrator User Manual (Redirect to Help Guide)
     * Accessible at /admin/manual (Requires auth:admin)
     */
    public function admin()
    {
        return redirect()->to(base_url('admin/help'));
    }

    /**
     * Dispatcher (Staff) User Manual (Redirect to Help Guide)
     * Accessible at /staff/manual (Requires auth:staff)
     */
    public function staff()
    {
        return redirect()->to(base_url('staff/help'));
    }

    /**
     * Administrator & Super Administrator Simple Help Guide
     * Accessible at /admin/help (Requires auth:admin)
     */
    public function adminHelp()
    {
        $data = [
            'title' => 'Administrator Quick Help Guide',
            'role'  => session()->get('role') ?? 'admin',
        ];

        return view('help/admin', $data);
    }

    /**
     * Dispatcher (Staff) Simple Help Guide
     * Accessible at /staff/help (Requires auth:staff)
     */
    public function staffHelp()
    {
        $data = [
            'title' => 'Dispatcher Quick Help Guide',
            'role'  => 'staff',
        ];

        return view('help/dispatcher', $data);
    }
}

