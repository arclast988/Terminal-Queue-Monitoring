<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AnnouncementModel;
use App\Models\TerminalModel;

class Announcements extends BaseController
{
    protected $announcementModel;
    protected $terminalModel;

    public function __construct()
    {
        $this->announcementModel = new AnnouncementModel();
        $this->terminalModel     = new TerminalModel();
    }

    public function index()
    {
        try {
            $announcements = $this->announcementModel
                ->select('announcements.*, terminals.name as terminal_name')
                ->join('terminals', 'terminals.id = announcements.terminal_id', 'left')
                ->orderBy('sort_order', 'ASC')
                ->orderBy('announcements.id', 'DESC')
                ->findAll();
        } catch (\Throwable $e) {
            return view('admin/announcements/setup_required', [
                'title' => 'Announcements - Setup Required'
            ]);
        }

        $data = [
            'title'         => 'Announcements',
            'announcements' => $announcements
        ];

        return view('admin/announcements/index', $data);
    }

    public function create()
    {
        $data = [
            'title'     => 'Add Announcement',
            'terminals' => $this->terminalModel->findAll(),
        ];
        return view('admin/announcements/create', $data);
    }

    public function store()
    {
        $rules = [
            'message'     => 'required|min_length[3]|max_length[2000]',
            'is_active'   => 'permit_empty|in_list[0,1]',
            'terminal_id' => 'required|integer|is_not_unique[terminals.id]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $maxOrder  = $this->announcementModel->selectMax('sort_order')->first();
            $sortOrder = isset($maxOrder['sort_order']) ? (int)$maxOrder['sort_order'] + 1 : 0;

            $this->announcementModel->insert([
                'terminal_id' => $this->request->getPost('terminal_id'),
                'message'     => $this->request->getPost('message'),
                'is_active'   => $this->request->getPost('is_active') ? 1 : 0,
                'sort_order'  => $sortOrder
            ]);
        } catch (\Throwable $e) {
            return redirect()->to('/admin/announcements')->with('error', 'Announcements table not found. Run the SQL shown on the Announcements page first.');
        }

        $msg = $this->request->getPost('message');
        $this->logActivity('Create announcement', substr($msg, 0, 80) . (strlen($msg) > 80 ? '…' : ''));

        // Clear the cached announcements feed + nudge clients so guests see it live.
        $this->broadcastUpdate('announcement_update', ['action' => 'create']);

        return redirect()->to('/admin/announcements')->with('success', 'Announcement added successfully.');
    }

    public function edit($id)
    {
        try {
            $announcement = $this->announcementModel->find($id);
        } catch (\Throwable $e) {
            return redirect()->to('/admin/announcements')->with('error', 'Announcements table not found. Run the SQL shown on the Announcements page first.');
        }

        if (!$announcement) {
            return redirect()->to('/admin/announcements')->with('error', 'Announcement not found.');
        }

        $data = [
            'title'        => 'Edit Announcement',
            'announcement' => $announcement,
            'terminals'    => $this->terminalModel->findAll(),
        ];

        return view('admin/announcements/edit', $data);
    }

    public function update($id)
    {
        $rules = [
            'message'     => 'required|min_length[3]|max_length[2000]',
            'is_active'   => 'permit_empty|in_list[0,1]',
            'terminal_id' => 'required|integer|is_not_unique[terminals.id]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $this->announcementModel->update($id, [
                'terminal_id' => $this->request->getPost('terminal_id'),
                'message'     => $this->request->getPost('message'),
                'is_active'   => $this->request->getPost('is_active') ? 1 : 0
            ]);
        } catch (\Throwable $e) {
            return redirect()->to('/admin/announcements')->with('error', 'Announcements table not found. Run the SQL shown on the Announcements page first.');
        }

        $msg = $this->request->getPost('message');
        $this->logActivity('Update announcement', 'ID ' . $id . ': ' . substr($msg, 0, 60) . (strlen($msg) > 60 ? '…' : ''));

        $this->broadcastUpdate('announcement_update', ['action' => 'update', 'id' => (int) $id]);

        return redirect()->to('/admin/announcements')->with('success', 'Announcement updated successfully.');
    }

    public function delete($id)
    {
        try {
            $a = $this->announcementModel->find($id);
            if ($this->announcementModel->delete($id)) {
                if ($a) {
                    $this->logActivity('Delete announcement', 'ID ' . $id . ': ' . substr($a['message'], 0, 50) . (strlen($a['message']) > 50 ? '…' : ''));
                }
                $this->broadcastUpdate('announcement_update', ['action' => 'delete', 'id' => (int) $id]);
                return redirect()->to('/admin/announcements')->with('success', 'Announcement deleted successfully.');
            }
        } catch (\Throwable $e) {
            return redirect()->to('/admin/announcements')->with('error', 'Announcements table not found. Run the SQL shown on the Announcements page first.');
        }
        return redirect()->to('/admin/announcements')->with('error', 'Failed to delete announcement.');
    }
}
