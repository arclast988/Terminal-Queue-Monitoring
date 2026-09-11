<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class AdminUsersViewTest extends CIUnitTestCase
{
    public function testUsersTableRenderingAndRolePills(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'id'         => 1,
            'role'       => 'super_admin',
            'full_name'  => 'System Super Admin',
            'username'   => 'superadmin@palompon.com',
        ]);

        $users = [
            [
                'id'                     => 1,
                'full_name'              => 'System Super Admin',
                'username'               => 'superadmin@palompon.com',
                'role'                   => 'super_admin',
                'profile_image'          => 'uploads/avatars/avatar_1_test.png',
                'assigned_routes_label'  => 'All Routes',
            ],
            [
                'id'                     => 2,
                'full_name'              => 'Jane Administrator',
                'username'               => 'jane@palompon.com',
                'role'                   => 'admin',
                'profile_image'          => null,
                'assigned_routes_label'  => 'All Routes',
            ],
            [
                'id'                     => 3,
                'full_name'              => 'Bob Dispatcher',
                'username'               => 'bob@palompon.com',
                'role'                   => 'staff',
                'profile_image'          => null,
                'assigned_routes_label'  => 'Tacloban, Ormoc',
            ],
            [
                'id'                     => 4,
                'full_name'              => 'Alice Dispatcher',
                'username'               => 'alice@palompon.com',
                'role'                   => 'staff',
                'profile_image'          => null,
                'assigned_routes_label'  => 'None',
            ],
        ];

        $html = view('admin/users/index', [
            'title' => 'Manage Users',
            'users' => $users,
        ]);

        // 1. Check Role Pills have unified profile-role-pill structure and icons
        $this->assertStringContainsString('profile-role-pill pill-super_admin', $html);
        $this->assertStringContainsString('profile-role-pill pill-admin', $html);
        $this->assertStringContainsString('profile-role-pill pill-staff', $html);
        $this->assertStringContainsString('fa-shield-alt', $html);
        $this->assertStringContainsString('fa-user-gear', $html);

        // 2. Super Admin and Admin should NOT have "All Routes" badge; should display muted dash (— / &mdash;)
        $this->assertStringNotContainsString('badge-modern badge-modern-success">All Routes</span>', $html);
        $this->assertStringContainsString('&mdash;', $html);

        // 3. Dispatcher routes should be preserved
        $this->assertStringContainsString('Tacloban', $html);
        $this->assertStringContainsString('Ormoc', $html);
        $this->assertStringContainsString('No Routes', $html);

        // 4. Check (You) badge appears on active logged-in user (#1)
        $this->assertStringContainsString('>You</span>', $html);

        // 5. Check Avatar image rendered for user #1 with profile_image
        $this->assertStringContainsString('uploads/avatars/avatar_1_test.png', $html);

        // 6. Check Clean Email formatting (envelope icon, no double @)
        $this->assertStringContainsString('<i class="bi bi-envelope me-1"></i>superadmin@palompon.com', $html);
        $this->assertStringNotContainsString('@superadmin@palompon.com', $html);
    }

    public function testAdminHelpRolePillsAndIcons(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'id'         => 1,
            'role'       => 'admin',
            'full_name'  => 'Admin User',
            'username'   => 'admin@palompon.com',
        ]);

        $html = view('help/admin', [
            'title' => 'Administrator Quick Help Guide',
        ]);

        // Verify solid profile-role-pill structure and icons in help guide
        $this->assertStringContainsString('profile-role-pill pill-super_admin', $html);
        $this->assertStringContainsString('profile-role-pill pill-admin', $html);
        $this->assertStringContainsString('profile-role-pill pill-staff', $html);
        $this->assertStringContainsString('fa-shield-alt', $html);
        $this->assertStringContainsString('fa-user-gear', $html);

        // Verify no washed out pale-pink role badge
        $this->assertStringNotContainsString('.role-super { background: #fee2e2;', $html);
        $this->assertStringNotContainsString('<span class="role-badge role-super">', $html);
    }
    public function testUserArchiveLifecycleButtonsAndModals(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'id'         => 1,
            'role'       => 'super_admin',
            'full_name'  => 'System Super Admin',
            'username'   => 'superadmin@palompon.com',
        ]);

        $users = [
            [
                'id'                     => 1,
                'full_name'              => 'System Super Admin',
                'username'               => 'superadmin@palompon.com',
                'role'                   => 'super_admin',
                'status'                 => 'active',
                'profile_image'          => null,
                'assigned_routes_label'  => 'All Routes',
            ],
            [
                'id'                     => 2,
                'full_name'              => 'Active Dispatcher',
                'username'               => 'active_disp@palompon.com',
                'role'                   => 'staff',
                'status'                 => 'active',
                'profile_image'          => null,
                'assigned_routes_label'  => 'Tacloban',
            ],
            [
                'id'                     => 3,
                'full_name'              => 'Archived Dispatcher',
                'username'               => 'archived_disp@palompon.com',
                'role'                   => 'staff',
                'status'                 => 'archived',
                'profile_image'          => null,
                'assigned_routes_label'  => 'Ormoc',
            ],
        ];

        $html = view('admin/users/index', [
            'title'           => 'Manage Users',
            'users'           => $users,
            'countActive'     => 2,
            'countAdmin'      => 1,
            'countDispatcher' => 1,
            'countArchived'   => 1,
        ]);

        // 1. Filter chips
        $this->assertStringContainsString('id="filter-btn-all"', $html);
        $this->assertStringContainsString('id="filter-btn-admin"', $html);
        $this->assertStringContainsString('id="filter-btn-dispatcher"', $html);
        $this->assertStringContainsString('id="filter-btn-archived"', $html);
        $this->assertStringContainsString('Archived', $html);

        // 2. Active dispatcher should have [Edit] and [Deactivate]
        $this->assertStringContainsString('showDeactivateUserModal', $html);
        $this->assertStringContainsString('btn-action-deactivate', $html);
        $this->assertStringContainsString('admin/users/deactivate/2', $html);

        // 3. Archived dispatcher should have [Activate], [Edit], and [Delete]
        $this->assertStringContainsString('showActivateUserModal', $html);
        $this->assertStringContainsString('btn-action-activate', $html);
        $this->assertStringContainsString('admin/users/activate/3', $html);
        $this->assertStringContainsString('btn-action-delete', $html);
        $this->assertStringContainsString('admin/users/delete/3', $html);

        // 4. Modals presence
        $this->assertStringContainsString('id="deactivateUserConfirmModal"', $html);
        $this->assertStringContainsString('id="activateUserConfirmModal"', $html);
        $this->assertStringContainsString('id="deleteUserConfirmModal"', $html);
        $this->assertStringContainsString('Permanently Delete User?', $html);
    }

    public function testGuestHeaderAnnouncementBarEnlarged(): void
    {
        $html = view('templates/guest_header', [
            'title' => 'Home',
        ]);

        // Verify enlarged, bold marquee advisory bar
        $this->assertStringContainsString('min-height: 42px', $html);
        $this->assertStringContainsString('font-weight: 700', $html);
        $this->assertStringContainsString('font-size: 16px', $html);
        $this->assertStringContainsString('width: 28px;', $html);
        $this->assertStringContainsString('height: 28px;', $html);
    }

    public function testAutoRetentionPillInLogsAndHistory(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'id'         => 1,
            'role'       => 'admin',
            'full_name'  => 'Admin User',
            'username'   => 'admin@palompon.com',
        ]);

        $logsHtml = view('admin/logs/index', [
            'title'       => 'Activity Logs',
            'logs'        => [],
            'actions'     => [],
            'users'       => [],
            'filter_type' => 'all',
            'search'      => '',
            'from_date'   => '',
            'to_date'     => '',
            'pager'       => null,
        ]);

        $this->assertStringContainsString('Auto-Retention: Logs are automatically kept for 60 days', $logsHtml);
        $this->assertStringContainsString('retention-pill', $logsHtml);

        $historyHtml = view('admin/history/index', [
            'title'        => 'Departure History',
            'history'      => [],
            'origins'      => [],
            'destinations' => [],
            'drivers'      => [],
            'vehicles'     => [],
            'vehicleTypes' => [],
            'pager'        => null,
            'filterOrigin' => '',
            'filterDest'   => '',
            'filterVehicle'=> '',
            'filterDriver' => '',
            'filterDate'   => '',
            'filterSearch' => '',
        ]);

        $this->assertStringContainsString('Auto-Retention: Departure records are automatically kept for 60 days', $historyHtml);
        $this->assertStringContainsString('retention-pill', $historyHtml);
    }

    public function testChangePasswordEnterpriseLayout(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'id'         => 1,
            'role'       => 'staff',
            'full_name'  => 'Juan Dela Cruz',
            'username'   => 'juan@palompon.com',
        ]);

        $html = view('auth/change_password', [
            'title'        => 'Change Password',
            'user'         => [
                'id'            => 1,
                'username'      => 'juan@palompon.com',
                'full_name'     => 'Juan Dela Cruz',
                'role'          => 'staff',
                'profile_image' => null,
            ],
            'masked_email' => 'j***@palompon.com',
            'has_email'    => true,
        ]);

        // Verify enterprise components
        $this->assertStringContainsString('Account Security & Password', $html);
        $this->assertStringContainsString('Security Guidelines', $html);
        $this->assertStringContainsString('Update Password', $html);
        $this->assertStringContainsString('Back to Dashboard', $html);
        $this->assertStringContainsString('btn-modern-outline', $html);
        $this->assertStringContainsString('btnSendOtp', $html);
        $this->assertStringContainsString('Get Code', $html);
        $this->assertStringContainsString('verification_code', $html);
    }

    public function testArchivedUserStatusLogic(): void
    {
        $activeUser = [
            'id'       => 10,
            'username' => 'active@palompon.com',
            'status'   => 'active',
        ];
        $archivedUser = [
            'id'       => 11,
            'username' => 'archived@palompon.com',
            'status'   => 'archived',
        ];

        $this->assertFalse(($activeUser['status'] ?? 'active') === 'archived');
        $this->assertTrue(($archivedUser['status'] ?? 'active') === 'archived');
    }

    public function testDeactivateButtonStylingIsYellowishOrangeNotGreen(): void
    {
        $responsiveCss = file_get_contents(FCPATH . 'assets/css/responsive.css');
        $this->assertNotFalse($responsiveCss);

        // Ensure button.btn-modern[title*="activate" i] excludes deactivate
        $this->assertStringContainsString('button.btn-modern[title*="activate" i]:not([title*="deactivate" i])', $responsiveCss);

        // Ensure deactivate button has yellowish orange / amber colors (#fef3c7 and #d97706)
        $modernCss = file_get_contents(FCPATH . 'assets/css/modern-frontend.css');
        $this->assertNotFalse($modernCss);
        $this->assertStringContainsString('.btn-action-deactivate', $modernCss);
        $this->assertStringContainsString('background: #fef3c7 !important;', $modernCss);
        $this->assertStringContainsString('color: #d97706 !important;', $modernCss);
        $this->assertStringContainsString('background: #f59e0b !important;', $modernCss);
    }
}
