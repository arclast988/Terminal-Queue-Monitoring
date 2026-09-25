<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class TerminalAndAnnouncementBulkDeleteTest extends CIUnitTestCase
{
    public function testTerminalBulkDeleteElementsRenderedForAdmin(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'role'       => 'admin',
            'full_name'  => 'Administrator',
            'username'   => 'admin',
        ]);

        $mockTerminals = [
            [
                'id'         => 1,
                'name'       => 'North Terminal',
                'location'   => 'Downtown',
                'capacity'   => 25,
                'created_at' => '2026-09-01 10:00:00',
            ],
            [
                'id'         => 2,
                'name'       => 'South Terminal',
                'location'   => 'Uptown',
                'capacity'   => 15,
                'created_at' => '2026-09-02 11:30:00',
            ],
        ];

        $html = view('admin/terminals/index', [
            'title'     => 'Terminals Management',
            'terminals' => $mockTerminals,
        ]);

        // Toggle button in header labeled "Select"
        $this->assertStringContainsString('id="btn-toggle-select-terminals"', $html);
        $this->assertStringContainsString('toggleTerminalSelectMode()', $html);
        $this->assertStringContainsString('id="btn-select-terminals-text">Select</span>', $html);

        // Bulk toolbar above table
        $this->assertStringContainsString('id="terminal-bulk-toolbar"', $html);
        $this->assertStringContainsString('id="select-all-terminals"', $html);
        $this->assertStringContainsString('id="terminal-selected-count"', $html);
        $this->assertStringContainsString('id="btn-bulk-delete-terminals"', $html);
        $this->assertStringContainsString('openTerminalBulkModal()', $html);
        $this->assertStringContainsString('Delete Selected', $html);

        // Checkbox column in thead and tbody
        $this->assertStringContainsString('<th class="bulk-col"', $html);
        $this->assertStringContainsString('class="form-check-input terminal-row-checkbox"', $html);
        $this->assertStringContainsString('data-name="North Terminal"', $html);
        $this->assertStringContainsString('data-name="South Terminal"', $html);

        // Bulk confirm modal
        $this->assertStringContainsString('id="terminalBulkConfirmModal"', $html);
        $this->assertStringContainsString('admin/terminals/bulk-action', $html);
        $this->assertStringContainsString('id="terminalBulkForm"', $html);
        $this->assertStringContainsString('id="terminalBulkIdsContainer"', $html);
        $this->assertStringContainsString('id="terminalBulkSubmitBtn"', $html);
    }

    public function testAnnouncementBulkDeleteElementsRenderedForStaff(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'role'       => 'staff',
            'full_name'  => 'Dispatcher One',
            'username'   => 'staff1',
        ]);

        $mockAnnouncements = [
            [
                'id'            => 10,
                'terminal_id'   => 1,
                'terminal_name' => 'VILLABA',
                'severity'      => 'danger',
                'message'       => 'Terminal Maintenance Notice',
                'is_active'     => 1,
                'created_at'    => '2026-09-20 09:00:00',
            ],
            [
                'id'            => 11,
                'terminal_id'   => 1,
                'terminal_name' => 'VILLABA',
                'severity'      => 'info',
                'message'       => 'Queue update available',
                'is_active'     => 1,
                'created_at'    => '2026-09-20 10:15:00',
            ],
        ];

        $html = view('admin/announcements/index', [
            'title'         => 'Announcements',
            'announcements' => $mockAnnouncements,
        ]);

        // Toggle button in header labeled "Select"
        $this->assertStringContainsString('id="btn-toggle-select-announcements"', $html);
        $this->assertStringContainsString('toggleAnnouncementSelectMode()', $html);
        $this->assertStringContainsString('id="btn-select-announcements-text">Select</span>', $html);

        // Bulk toolbar above table
        $this->assertStringContainsString('id="announcement-bulk-toolbar"', $html);
        $this->assertStringContainsString('id="select-all-announcements"', $html);
        $this->assertStringContainsString('id="announcement-selected-count"', $html);
        $this->assertStringContainsString('id="btn-bulk-delete-announcements"', $html);
        $this->assertStringContainsString('openAnnouncementBulkModal()', $html);
        $this->assertStringContainsString('Delete Selected', $html);

        // Checkbox column in thead and tbody
        $this->assertStringContainsString('<th class="bulk-col"', $html);
        $this->assertStringContainsString('class="form-check-input announcement-row-checkbox"', $html);
        $this->assertStringContainsString('value="10"', $html);
        $this->assertStringContainsString('value="11"', $html);

        // Bulk confirm modal
        $this->assertStringContainsString('id="announcementBulkConfirmModal"', $html);
        $this->assertStringContainsString('admin/announcements/bulk-action', $html);
        $this->assertStringContainsString('id="announcementBulkForm"', $html);
        $this->assertStringContainsString('id="announcementBulkIdsContainer"', $html);
        $this->assertStringContainsString('id="announcementBulkSubmitBtn"', $html);
    }

    public function testControllersHaveBulkActionMethods(): void
    {
        $this->assertTrue(method_exists(\App\Controllers\Admin\Terminals::class, 'bulkAction'));
        $this->assertTrue(method_exists(\App\Controllers\Admin\Announcements::class, 'bulkAction'));
    }
}
