<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class UserAvatarAndRoleThemeTest extends CIUnitTestCase
{
    public function testSuperAdminAvatarSelfEditPermissionOnly(): void
    {
        $session = service('session');

        // 1. Logged in as regular Admin (id: 2)
        $session->set([
            'isLoggedIn' => true,
            'id'         => 2,
            'role'       => 'admin',
            'full_name'  => 'Regular Admin',
            'username'   => 'admin@palompon.com',
        ]);

        $users = [
            [
                'id'            => 1,
                'full_name'     => 'The Super Admin',
                'username'      => 'superadmin@palompon.com',
                'role'          => 'super_admin',
                'profile_image' => null,
            ],
            [
                'id'            => 2,
                'full_name'     => 'Regular Admin',
                'username'      => 'admin@palompon.com',
                'role'          => 'admin',
                'profile_image' => null,
            ],
            [
                'id'            => 3,
                'full_name'     => 'Staff Dispatcher',
                'username'      => 'dispatcher@palompon.com',
                'role'          => 'staff',
                'profile_image' => null,
            ],
        ];

        $html = view('admin/users/index', [
            'title'           => 'Manage Users',
            'users'           => $users,
            'countActive'     => 3,
            'countAdmin'      => 2,
            'countDispatcher' => 1,
            'countArchived'   => 0,
        ]);

        // Regular admin (id: 2) CANNOT edit Super Admin's avatar (id: 1)
        $this->assertStringContainsString('id="avatar-wrapper-1"', $html);
        $this->assertStringNotContainsString('class="avatar-wrapper me-3 avatar-editable" id="avatar-wrapper-1"', $html);

        // Regular admin (id: 2) CAN edit their own avatar (id: 2)
        $this->assertStringContainsString('class="avatar-wrapper me-3 avatar-editable" id="avatar-wrapper-2"', $html);

        // Regular admin (id: 2) CAN edit Dispatcher avatar (id: 3)
        $this->assertStringContainsString('class="avatar-wrapper me-3 avatar-editable" id="avatar-wrapper-3"', $html);

        // 2. Logged in as Super Admin (id: 1)
        $session->set([
            'isLoggedIn' => true,
            'id'         => 1,
            'role'       => 'super_admin',
            'full_name'  => 'The Super Admin',
            'username'   => 'superadmin@palompon.com',
        ]);

        $htmlSuper = view('admin/users/index', [
            'title'           => 'Manage Users',
            'users'           => $users,
            'countActive'     => 3,
            'countAdmin'      => 2,
            'countDispatcher' => 1,
            'countArchived'   => 0,
        ]);

        // Super Admin CAN edit their own avatar (id: 1)
        $this->assertStringContainsString('class="avatar-wrapper me-3 avatar-editable" id="avatar-wrapper-1"', $htmlSuper);
        // Super Admin CAN edit admin #2 and dispatcher #3
        $this->assertStringContainsString('class="avatar-wrapper me-3 avatar-editable" id="avatar-wrapper-2"', $htmlSuper);
        $this->assertStringContainsString('class="avatar-wrapper me-3 avatar-editable" id="avatar-wrapper-3"', $htmlSuper);

        // Avatar circle is enlarged to 44px
        $this->assertStringContainsString('width: 44px;', $htmlSuper);
        $this->assertStringContainsString('height: 44px;', $htmlSuper);
        $this->assertStringContainsString('font-size: 15px;', $htmlSuper);

        // Dispatchers filter tab has vf-dispatcher and active Emerald Green styling (#15803d)
        $this->assertStringContainsString('class="vf-btn vf-dispatcher"', $htmlSuper);
        $this->assertStringContainsString('.vf-btn.vf-dispatcher.active', $htmlSuper);
        $this->assertStringContainsString('#15803d', $htmlSuper);

        // Modal for avatar upload is present
        $this->assertStringContainsString('id="userAvatarModal"', $htmlSuper);
    }

    public function testRouteActiveTabAdminTheme(): void
    {
        $html = view('admin/routes/index', [
            'title'               => 'Route Management',
            'groupedRoutes'       => [],
            'countActiveGroups'   => 3,
            'countArchivedGroups' => 0,
        ]);

        // Active Routes tab uses Admin Red theme color (#b71c1c / var(--primary-red))
        $this->assertStringContainsString('#tab-active-routes.active', $html);
        $this->assertStringContainsString('background: #b71c1c !important;', $html);
        $this->assertStringNotContainsString('#tab-active-routes.active {\n        background: #15803d !important;', $html);
    }

    public function testChangePasswordPageLabelsAndAlerts(): void
    {
        $content = file_get_contents(APPPATH . 'Views/auth/change_password.php');

        // "Username / Email" is combined into a single line
        $this->assertStringContainsString('Username / Email:', $content);
        $this->assertStringNotContainsString('<div>Username:</div>', $content);

        // No unwanted green OTP banner
        $this->assertStringNotContainsString('Enter the 6-digit code', $content);

        // Dispatcher avatar background is emerald green (#15803d)
        $this->assertStringContainsString('background: #15803d;', $content);
    }
}
