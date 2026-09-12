<?php

use CodeIgniter\Test\CIUnitTestCase;
use App\Models\AnnouncementModel;

/**
 * @internal
 */
final class AnnouncementSeverityTest extends CIUnitTestCase
{
    public function testAnnouncementModelAllowedFieldsIncludesSeverity(): void
    {
        $model = new AnnouncementModel();
        $this->assertContains('severity', $model->allowedFields);
    }

    public function testAdminAnnouncementsIndexRendersSeverityBadges(): void
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
                'id'            => 10,
                'terminal_id'   => 1,
                'terminal_name' => 'PALOMPON TERMINAL',
                'message'       => 'Regular trip schedules in effect',
                'severity'      => 'info',
                'is_active'     => 1,
                'created_at'    => '2026-09-12 08:00:00',
            ],
            [
                'id'            => 11,
                'terminal_id'   => 1,
                'terminal_name' => 'PALOMPON TERMINAL',
                'message'       => 'Expect 15 minute delay due to road repairs',
                'severity'      => 'warning',
                'is_active'     => 1,
                'created_at'    => '2026-09-12 08:15:00',
            ],
            [
                'id'            => 12,
                'terminal_id'   => 1,
                'terminal_name' => 'PALOMPON TERMINAL',
                'message'       => 'Trips to Ormoc suspended due to typhoon signal',
                'severity'      => 'danger',
                'is_active'     => 1,
                'created_at'    => '2026-09-12 08:30:00',
            ],
        ];

        $html = view('admin/announcements/index', [
            'title'         => 'Announcements',
            'announcements' => $mockAnnouncements,
        ]);

        // Check Table Header
        $this->assertStringContainsString('<th>Severity</th>', $html);

        // Check Info badge
        $this->assertStringContainsString('badge-modern-info', $html);
        $this->assertStringContainsString('Info', $html);

        // Check Warning badge
        $this->assertStringContainsString('badge-modern-warning', $html);
        $this->assertStringContainsString('Warning', $html);

        // Check Urgent / Danger badge
        $this->assertStringContainsString('badge-modern-danger', $html);
        $this->assertStringContainsString('Urgent', $html);
    }

    public function testAdminAnnouncementsCreateRendersSeverityOptions(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'role'       => 'admin',
            'full_name'  => 'Admin User',
            'username'   => 'admin',
        ]);

        $html = view('admin/announcements/create', [
            'title'     => 'Add Announcement',
            'terminals' => [
                ['id' => 1, 'name' => 'Palompon Terminal'],
            ],
        ]);

        $this->assertStringContainsString('Severity Level', $html);
        $this->assertStringContainsString('value="info"', $html);
        $this->assertStringContainsString('value="warning"', $html);
        $this->assertStringContainsString('value="danger"', $html);
        $this->assertStringContainsString('severity-options-grid', $html);
        $this->assertStringContainsString('Info / Normal', $html);
        $this->assertStringContainsString('Urgent / Alert', $html);
    }

    public function testAdminAnnouncementsEditRendersCurrentSeveritySelected(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'role'       => 'admin',
            'full_name'  => 'Admin User',
            'username'   => 'admin',
        ]);

        $html = view('admin/announcements/edit', [
            'title'        => 'Edit Announcement',
            'announcement' => [
                'id'          => 20,
                'terminal_id' => 1,
                'message'     => 'Severe storm warning',
                'severity'    => 'danger',
                'is_active'   => 1,
            ],
            'terminals'    => [
                ['id' => 1, 'name' => 'Palompon Terminal'],
            ],
        ]);

        $this->assertStringContainsString('Severity Level', $html);
        $this->assertStringContainsString('value="danger" checked', $html);
        $this->assertStringContainsString('data-severity="danger"', $html);
    }

    public function testGuestHeaderRendersSeverityInMarqueeAndModal(): void
    {
        $mockAnnouncements = [
            [
                'id'          => 1,
                'message'     => 'Ormoc trips running on schedule',
                'severity'    => 'info',
                'is_active'   => 1,
            ],
            [
                'id'          => 2,
                'message'     => 'Heavy traffic along highway',
                'severity'    => 'warning',
                'is_active'   => 1,
            ],
            [
                'id'          => 3,
                'message'     => 'Tacloban route suspended',
                'severity'    => 'danger',
                'is_active'   => 1,
            ],
        ];

        $html = view('templates/guest_header', [
            'title'         => 'Test Guest Page',
            'announcements' => $mockAnnouncements,
        ]);

        // Marquee should prefix [WARNING] and [URGENT]
        $this->assertStringContainsString('[WARNING] Heavy traffic along highway', $html);
        $this->assertStringContainsString('[URGENT] Tacloban route suspended', $html);
        $this->assertStringContainsString('Ormoc trips running on schedule', $html);

        // Modal list should have severity classes and tags
        $this->assertStringContainsString('ann-item-danger', $html);
        $this->assertStringContainsString('ann-item-warning', $html);
        $this->assertStringContainsString('ann-item-info', $html);
        $this->assertStringContainsString('ann-tag-danger', $html);
        $this->assertStringContainsString('ann-tag-warning', $html);
        $this->assertStringContainsString('ann-tag-info', $html);
    }
}
