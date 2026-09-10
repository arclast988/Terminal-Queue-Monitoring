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
}
