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
        $this->assertStringContainsString('id="removeAvatarConfirmModal"', $html);
        $this->assertStringContainsString('Remove Profile Photo?', $html);
        $this->assertStringContainsString('Are you sure you want to revert your profile picture to the default avatar?', $html);
        $this->assertStringContainsString('fa-triangle-exclamation', $html);
        $this->assertStringContainsString('id="removeAvatarConfirmBtn"', $html);
        $this->assertStringNotContainsString('id="removeAvatarItemChip"', $html);
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

    public function testCreateUserFormAvatarUploadAndDefaultSilhouetteAndNoCameraBadge(): void
    {
        // 1. Verify Create form includes profile avatar upload and default silhouette
        $createHtml = view('admin/users/create', [
            'routes' => [],
        ]);

        $this->assertStringContainsString('enctype="multipart/form-data"', $createHtml);
        $this->assertStringContainsString('id="createUserAvatarPreview"', $createHtml);
        $this->assertStringContainsString('id="createAvatarFileInput"', $createHtml);
        $this->assertStringContainsString('name="avatar"', $createHtml);
        $this->assertStringContainsString('profile-identicon-svg', $createHtml);

        // 2. Verify Index users list uses default silhouette SVG when profile_image is null
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
                'id'            => 2,
                'full_name'     => 'Jane Administrator',
                'username'      => 'jane@palompon.com',
                'role'          => 'admin',
                'profile_image' => null,
            ],
        ];

        $indexHtml = view('admin/users/index', [
            'title' => 'Manage Users',
            'users' => $users,
        ]);

        // Consistent executive user avatar silhouette is rendered for user #2
        $this->assertStringContainsString('profile-identicon-svg', $indexHtml);
        $this->assertStringNotContainsString('<span>JA</span>', $indexHtml);

        // Camera badge is removed from the avatar, but wrapper remains clickable
        $this->assertStringNotContainsString('avatar-edit-badge', $indexHtml);
        $this->assertStringNotContainsString('avatar-hover-overlay', $indexHtml);
        $this->assertStringContainsString('avatar-editable', $indexHtml);
        $this->assertStringContainsString('openAvatarModal', $indexHtml);

        // Modal Remove Photo button has d-none and display: none !important
        $this->assertStringContainsString('id="modalRemoveAvatarBtn" style="border-radius: 8px; font-weight: 600; padding: 7px 14px; display: none !important;"', $indexHtml);
        $this->assertStringContainsString('class="btn btn-outline-danger btn-sm d-none"', $indexHtml);

        // Responsive CSS protects modalRemoveAvatarBtn from mobile display override
        $responsiveCss = file_get_contents(FCPATH . 'assets/css/responsive.css');
        $this->assertStringContainsString('#modalRemoveAvatarBtn.d-none', $responsiveCss);
    }

    public function testEditUserViewRendersSuccessfullyForArchivedAndActiveUsers(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'id'         => 1,
            'role'       => 'super_admin',
            'full_name'  => 'System Super Admin',
            'username'   => 'superadmin@palompon.com',
        ]);

        $archivedUser = [
            'id'            => 4,
            'full_name'     => 'Alice Dispatcher',
            'username'      => 'alice@palompon.com',
            'role'          => 'staff',
            'status'        => 'archived',
            'profile_image' => null,
        ];

        $html = view('admin/users/edit', [
            'user'             => $archivedUser,
            'routes'           => [],
            'assignedRouteIds' => [],
        ]);

        $this->assertStringContainsString('Edit User', $html);
        $this->assertStringContainsString('Alice Dispatcher', $html);
        $this->assertStringContainsString('profile-identicon-svg', $html);
        $this->assertStringContainsString('admin/users/update/4', $html);
    }

    public function testAvatarDynamicSyncAttributesAndScript(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'id'         => 1,
            'role'       => 'super_admin',
            'full_name'  => 'System Administrator',
            'username'   => 'admin',
        ]);

        $users = [
            [
                'id'            => 1,
                'full_name'     => 'System Administrator',
                'username'      => 'admin',
                'role'          => 'super_admin',
                'status'        => 'active',
                'profile_image' => 'uploads/avatars/avatar_admin.png',
            ],
            [
                'id'            => 2,
                'full_name'     => 'Dispatcher User',
                'username'      => 'dispatcher',
                'role'          => 'staff',
                'status'        => 'active',
                'profile_image' => null,
            ],
        ];

        $html = view('admin/users/index', [
            'users' => $users,
        ]);

        // Check data attributes on avatar-wrapper
        $this->assertStringContainsString('data-user-id="1"', $html);
        $this->assertStringContainsString('data-img-url="http://localhost/uploads/avatars/avatar_admin.png"', $html);
        $this->assertStringContainsString('data-user-id="2"', $html);
        $this->assertStringContainsString('data-img-url=""', $html);

        // Check synchronization functions
        $this->assertStringContainsString('window.updateModalAvatarPreview', $html);
        $this->assertStringContainsString('window.updateAvatarDom', $html);
    }
}

