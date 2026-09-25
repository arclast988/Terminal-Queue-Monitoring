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
        $this->assertStringContainsString('id="btn-toggle-select-announcements"', $html);
        $this->assertStringContainsString('toggleAnnouncementSelectMode()', $html);
        $this->assertStringContainsString('Select', $html);

        // Verify bulk delete confirmation modal
        $this->assertStringContainsString('id="announcementBulkConfirmModal"', $html);
        $this->assertStringContainsString('Delete Selected Announcements?', $html);
        $this->assertStringContainsString('admin/announcements/bulk-action', $html);
        $this->assertStringContainsString('id="announcementBulkForm"', $html);
        $this->assertStringContainsString('id="announcementBulkSubmitBtn"', $html);
        $this->assertStringContainsString('Yes, Delete Selected', $html);
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
        $this->assertStringContainsString('id="btn-toggle-select-announcements"', $html);
        $this->assertStringContainsString('Select', $html);
        $this->assertStringContainsString('id="announcementBulkConfirmModal"', $html);
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

        $this->assertStringContainsString('id="btn-toggle-select-announcements"', $html);
        $this->assertStringContainsString('disabled', $html);
    }

    public function testAnnouncementsControllerHasDeleteAllMethod(): void
    {
        $this->assertTrue(
            method_exists(\App\Controllers\Admin\Announcements::class, 'bulkAction') ||
            method_exists(\App\Controllers\Admin\Announcements::class, 'deleteAll')
        );
    }
}
