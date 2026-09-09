<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AnnouncementsDeleteAllTest extends CIUnitTestCase
{
    public function testAdminAnnouncementListRendersDeleteAllButton(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'role'       => 'super_admin',
            'full_name'  => 'System Administrator',
            'username'   => 'admin',
        ]);

        $mockAnnouncements = [
            [
                'id'            => 1,
                'terminal_id'   => 1,
                'terminal_name' => 'PALOMPON TERMINAL',
                'message'       => 'Welcome to Palompon Transit',
                'is_active'     => 1,
                'created_at'    => '2026-09-04 07:58:00',
            ],
            [
                'id'            => 2,
                'terminal_id'   => 1,
                'terminal_name' => 'PALOMPON TERMINAL',
                'message'       => 'Last trip for today is 4pm',
                'is_active'     => 1,
                'created_at'    => '2026-09-04 08:01:00',
            ],
        ];

        $html = view('admin/announcements/index', [
            'title'         => 'Announcements',
            'announcements' => $mockAnnouncements,
        ]);

        $this->assertStringContainsString('Announcement List', $html);
        $this->assertStringContainsString('id="deleteAllAnnouncementsBtn"', $html);
        $this->assertStringContainsString('data-bs-target="#deleteAllAnnouncementsModal"', $html);
        $this->assertStringContainsString('confirmDeleteAll(event)', $html);
        $this->assertStringContainsString('Delete All', $html);
        $this->assertStringContainsString('bi-trash', $html);
        $this->assertStringContainsString('btn-action-delete', $html);

        // Verify custom confirmation modal
        $this->assertStringContainsString('id="deleteAllAnnouncementsModal"', $html);
        $this->assertStringContainsString('Delete All Announcements?', $html);
        $this->assertStringContainsString('admin/announcements/delete-all', $html);
        $this->assertStringContainsString('id="deleteAllAnnouncementsForm"', $html);
        $this->assertStringContainsString('id="btnConfirmDeleteAll"', $html);
        $this->assertStringContainsString('Yes, Delete All', $html);
        $this->assertStringContainsString('2</strong> announcements will be permanently deleted', $html);
    }

    public function testDispatcherAnnouncementListRendersDeleteAllButton(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'role'       => 'staff',
            'full_name'  => 'Rouge Omde',
            'username'   => 'staff',
        ]);

        $mockAnnouncements = [
            [
                'id'            => 1,
                'terminal_id'   => 1,
                'terminal_name' => 'PALOMPON TERMINAL',
                'message'       => 'Welcome to Palompon Transit',
                'is_active'     => 1,
                'created_at'    => '2026-09-04 07:58:00',
            ],
        ];

        $html = view('admin/announcements/index', [
            'title'         => 'Announcements',
            'announcements' => $mockAnnouncements,
        ]);

        $this->assertStringContainsString('Announcement List', $html);
        $this->assertStringContainsString('id="deleteAllAnnouncementsBtn"', $html);
        $this->assertStringContainsString('data-bs-target="#deleteAllAnnouncementsModal"', $html);
        $this->assertStringContainsString('Delete All', $html);
        $this->assertStringContainsString('id="deleteAllAnnouncementsModal"', $html);
        $this->assertStringContainsString('body.staff-theme .delete-all-btn-confirm', $html);
    }

    public function testEmptyAnnouncementsRendersDisabledDeleteAllButton(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'role'       => 'admin',
            'full_name'  => 'Admin User',
            'username'   => 'admin2',
        ]);

        $html = view('admin/announcements/index', [
            'title'         => 'Announcements',
            'announcements' => [],
        ]);

        $this->assertStringContainsString('id="deleteAllAnnouncementsBtn"', $html);
        $this->assertStringContainsString('disabled', $html);
        $this->assertStringContainsString('No announcements to delete', $html);
    }

    public function testAnnouncementsControllerHasDeleteAllMethod(): void
    {
        $this->assertTrue(method_exists(\App\Controllers\Admin\Announcements::class, 'deleteAll'));
    }
}
