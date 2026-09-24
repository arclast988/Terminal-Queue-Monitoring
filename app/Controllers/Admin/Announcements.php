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
            'severity'    => 'permit_empty|in_list[info,warning,danger]',
            'is_active'   => 'permit_empty|in_list[0,1]',
            'terminal_id' => 'required|integer|is_not_unique[terminals.id]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $maxOrder  = $this->announcementModel->selectMax('sort_order')->first();
            $sortOrder = isset($maxOrder['sort_order']) ? (int)$maxOrder['sort_order'] + 1 : 0;

            $severity = $this->request->getPost('severity');
            if (!in_array($severity, ['info', 'warning', 'danger'], true)) {
                $severity = 'info';
            }

            $this->announcementModel->insert([
                'terminal_id' => $this->request->getPost('terminal_id'),
                'message'     => $this->request->getPost('message'),
                'severity'    => $severity,
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
            'severity'    => 'permit_empty|in_list[info,warning,danger]',
            'is_active'   => 'permit_empty|in_list[0,1]',
            'terminal_id' => 'required|integer|is_not_unique[terminals.id]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $severity = $this->request->getPost('severity');
        if (!in_array($severity, ['info', 'warning', 'danger'], true)) {
            $severity = 'info';
        }

        $newData = [
            'terminal_id' => $this->request->getPost('terminal_id'),
            'message'     => $this->request->getPost('message'),
            'severity'    => $severity,
            'is_active'   => $this->request->getPost('is_active') ? 1 : 0,
        ];

        $current = $this->announcementModel->find($id);
        if ($current && $this->inputsUnchanged($current, $newData)) {
            return $this->noChangesResponse();
        }

        try {
            $this->announcementModel->update($id, $newData);
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
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin', 'staff'], true)) {
            return redirect()->to('/admin/announcements')->with('error', 'You do not have permission to delete announcements.');
        }

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

    public function deleteAll()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin', 'staff'], true)) {
            return redirect()->to('/admin/announcements')->with('error', 'You do not have permission to delete announcements.');
        }

        try {
            $count = $this->announcementModel->countAllResults();
            if ($count === 0) {
                return redirect()->to('/admin/announcements')->with('error', 'No announcements to delete.');
            }

            if ($this->announcementModel->where('id >', 0)->delete()) {
                $this->logActivity('Delete all announcements', "Deleted all ({$count}) announcements");
                $this->broadcastUpdate('announcement_update', ['action' => 'delete_all']);
                return redirect()->to('/admin/announcements')->with('success', 'All announcements deleted successfully.');
            }
        } catch (\Throwable $e) {
            return redirect()->to('/admin/announcements')->with('error', 'Announcements table not found or failed to delete.');
        }

        return redirect()->to('/admin/announcements')->with('error', 'Failed to delete all announcements.');
    }

    public function bulkAction()
    {
        $role = session()->get('role');
        if (!in_array($role, ['admin', 'super_admin', 'staff'], true)) {
            return redirect()->to('/admin/announcements')->with('error', 'You do not have permission to delete announcements.');
        }

        $rawIds = $this->request->getPost('ids');
        if (is_string($rawIds)) {
            $ids = array_filter(array_map('intval', explode(',', $rawIds)));
        } elseif (is_array($rawIds)) {
            $ids = array_filter(array_map('intval', $rawIds));
        } else {
            $ids = [];
        }

        if (empty($ids)) {
            return redirect()->to('/admin/announcements')->with('error', 'No announcements selected for deletion.');
        }

        try {
            $count = $this->announcementModel->whereIn('id', $ids)->countAllResults();
            if ($count === 0) {
                return redirect()->to('/admin/announcements')->with('error', 'Selected announcements not found.');
            }

            if ($this->announcementModel->whereIn('id', $ids)->delete()) {
                $this->logActivity('Bulk delete announcements', "Deleted $count announcement(s).");
                $this->broadcastUpdate('announcement_update', ['action' => 'bulk_delete', 'ids' => $ids]);
                return redirect()->to('/admin/announcements')->with('success', "$count announcement(s) deleted successfully.");
            }
        } catch (\Throwable $e) {
            return redirect()->to('/admin/announcements')->with('error', 'Failed to delete selected announcements.');
        }

        return redirect()->to('/admin/announcements')->with('error', 'Failed to delete selected announcements.');
    }
}
