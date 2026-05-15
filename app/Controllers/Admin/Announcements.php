<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AnnouncementModel;

class Announcements extends BaseController
{
    protected $announcementModel;

    public function __construct()
    {
        $this->announcementModel = new AnnouncementModel();
    }

    public function index()
    {
        try {
            $announcements = $this->announcementModel->orderBy('sort_order', 'ASC')->orderBy('id', 'DESC')->findAll();
        } catch (\Throwable $e) {
            return view('admin/announcements/setup_required', [
                'title' => 'Announcements - Setup Required'
            ]);
        }

        $data = [
            'title' => 'Announcements',
            'announcements' => $announcements
        ];

        return view('admin/announcements/index', $data);
    }

    public function create()
    {
        $data = ['title' => 'Add Announcement'];
        return view('admin/announcements/create', $data);
    }

    public function store()
    {
        $rules = [
            'message' => 'required|min_length[3]|max_length[2000]',
            'is_active' => 'permit_empty|in_list[0,1]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $maxOrder = $this->announcementModel->selectMax('sort_order')->first();
            $sortOrder = isset($maxOrder['sort_order']) ? (int)$maxOrder['sort_order'] + 1 : 0;

            $this->announcementModel->insert([
                'message' => $this->request->getPost('message'),
                'is_active' => $this->request->getPost('is_active') ? 1 : 0,
                'sort_order' => $sortOrder
            ]);
        } catch (\Throwable $e) {
            return redirect()->to('/admin/announcements')->with('error', 'Announcements table not found. Run the SQL shown on the Announcements page first.');
        }

        $msg = $this->request->getPost('message');
        $this->logActivity('Create announcement', substr($msg, 0, 80) . (strlen($msg) > 80 ? '…' : ''));

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
            'title' => 'Edit Announcement',
            'announcement' => $announcement
        ];

        return view('admin/announcements/edit', $data);
    }

    public function update($id)
    {
        $rules = [
            'message' => 'required|min_length[3]|max_length[2000]',
            'is_active' => 'permit_empty|in_list[0,1]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        try {
            $this->announcementModel->update($id, [
                'message' => $this->request->getPost('message'),
                'is_active' => $this->request->getPost('is_active') ? 1 : 0
            ]);
        } catch (\Throwable $e) {
            return redirect()->to('/admin/announcements')->with('error', 'Announcements table not found. Run the SQL shown on the Announcements page first.');
        }

        $msg = $this->request->getPost('message');
        $this->logActivity('Update announcement', 'ID ' . $id . ': ' . substr($msg, 0, 60) . (strlen($msg) > 60 ? '…' : ''));

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
                return redirect()->to('/admin/announcements')->with('success', 'Announcement deleted successfully.');
            }
        } catch (\Throwable $e) {
            return redirect()->to('/admin/announcements')->with('error', 'Announcements table not found. Run the SQL shown on the Announcements page first.');
        }
        return redirect()->to('/admin/announcements')->with('error', 'Failed to delete announcement.');
    }
}
