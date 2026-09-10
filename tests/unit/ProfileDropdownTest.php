<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ProfileDropdownTest extends CIUnitTestCase
{
    public function testAdminProfileDropdownStructure(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'role'       => 'super_admin',
            'full_name'  => 'System Administrator',
            'username'   => 'admin',
        ]);

        $html = view('partials/header');

        // Check Trigger: Icon on top, name directly below
        $this->assertStringContainsString('profile-trigger-btn', $html);
        $this->assertStringContainsString('profile-avatar-circle', $html);
        $this->assertStringContainsString('profile-identicon-svg', $html);
        $this->assertStringContainsString('profile-trigger-name', $html);
        $this->assertStringContainsString('System Administrator', $html);

        // Check Dropdown Card structure and content
        $this->assertStringContainsString('profile-dropdown-card', $html);
        $this->assertStringContainsString('Super Admin', $html);
        $this->assertStringContainsString('@admin', $html);
        $this->assertStringContainsString('Online', $html);

        // Admin should NOT have Change Password in dropdown (dispatcher-only)
        $this->assertStringContainsString('Activity Logs', $html);
        $this->assertStringContainsString('Departure History', $html);
        $this->assertStringContainsString('User Manual & Guide', $html);

        // Check badges
        $this->assertStringContainsString('Audit', $html);
        $this->assertStringContainsString('Records', $html);
        $this->assertStringContainsString('Docs', $html);

        // Check Logout trigger inside dropdown
        $this->assertStringContainsString('item-logout', $html);
        $this->assertStringContainsString('data-bs-target="#logoutModal"', $html);
        $this->assertStringContainsString('confirmLogout(event)', $html);
        $this->assertStringContainsString('Sign out', $html);

        // Check Avatar Click Mini-Menu (No big modal)
        $this->assertStringContainsString('btnHeaderAvatarClick', $html);
        $this->assertStringContainsString('avatarMiniMenu', $html);
        $this->assertStringContainsString('miniMenuEditBtn', $html);
        $this->assertStringContainsString('profileAvatarFileInput', $html);
    }

    public function testProfileDropdownWithCustomAvatarImage(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn'    => true,
            'role'          => 'admin',
            'full_name'     => 'Jane Admin',
            'username'      => 'jane_admin',
            'profile_image' => 'uploads/avatars/avatar_custom.png',
        ]);

        $html = view('partials/header');

        // Should render <img> with avatar URL instead of SVG identicon
        $this->assertStringContainsString('profile-avatar-img', $html);
        $this->assertStringContainsString('user-avatar-preview', $html);
        $this->assertStringContainsString('uploads/avatars/avatar_custom.png', $html);

        // Should show Delete (Default) button in mini-menu
        $this->assertStringContainsString('miniMenuDeleteBtn', $html);
        $this->assertStringContainsString('Delete (Default)', $html);
    }

    public function testDispatcherProfileDropdownStructure(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'role'       => 'staff',
            'full_name'  => 'Rouge Omde',
            'username'   => 'staff',
        ]);

        $html = view('partials/header');

        // Check Trigger
        $this->assertStringContainsString('profile-trigger-btn', $html);
        $this->assertStringContainsString('profile-avatar-circle', $html);
        $this->assertStringContainsString('Rouge Omde', $html);

        // Check Dropdown
        $this->assertStringContainsString('Dispatcher', $html);
        $this->assertStringContainsString('@staff', $html);
        $this->assertStringContainsString('Queue Management', $html);
        $this->assertStringContainsString('Change Password', $html);
        $this->assertStringContainsString('change-password', $html);
        $this->assertStringContainsString('User Manual & Guide', $html);
        $this->assertStringContainsString('Sign out', $html);
        $this->assertStringContainsString('confirmLogout(event)', $html);

        // Verify Departure History is NOT in the Dispatcher navigation or drawer
        $this->assertStringNotContainsString('admin/history', $html);
        $this->assertStringNotContainsString('Departure History', $html);

        // Check Avatar Click Mini-Menu
        $this->assertStringContainsString('btnHeaderAvatarClick', $html);
        $this->assertStringContainsString('avatarMiniMenu', $html);
        $this->assertStringContainsString('profileAvatarFileInput', $html);
    }

    public function testSidebarDrawerStructure(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'role'       => 'super_admin',
            'full_name'  => 'System Administrator',
            'username'   => 'admin',
        ]);

        $html = view('partials/header');

        // Drawer elements
        $this->assertStringContainsString('siteNavHamburgerBtn', $html);
        $this->assertStringContainsString('siteNavHamburgerIcon', $html);
        $this->assertStringContainsString('sidebarDrawerOverlay', $html);
        $this->assertStringContainsString('siteSidebarDrawer', $html);
        $this->assertStringContainsString('drawerCloseBtn', $html);
        $this->assertStringContainsString('drawer-nav-item', $html);

        // Navigation links inside drawer
        $this->assertStringContainsString('admin/dashboard', $html);
        $this->assertStringContainsString('admin/terminals', $html);
        $this->assertStringContainsString('admin/vehicles', $html);
        $this->assertStringContainsString('admin/routes', $html);
        $this->assertStringContainsString('admin/users', $html);
        $this->assertStringContainsString('admin/departure-rules', $html);
        $this->assertStringContainsString('admin/announcements', $html);
        $this->assertStringContainsString('schedules', $html);
        $this->assertStringContainsString('fares', $html);
        $this->assertStringContainsString('admin/logs', $html);
        $this->assertStringContainsString('admin/history', $html);

        // Drawer JS functions
        $this->assertStringContainsString('openSidebarDrawer', $html);
        $this->assertStringContainsString('closeSidebarDrawer', $html);
        $this->assertStringContainsString('toggleSidebarDrawer', $html);

        // Drawer close button should use fa-times (✕ icon), positioned after brand (on right)
        $this->assertMatchesRegularExpression('/drawerCloseBtn[^>]*>\s*<i class="fas fa-times"/s', $html);
        $this->assertTrue(strpos($html, 'drawer-brand-section') < strpos($html, 'drawerCloseBtn'));

        // Avatar in profile dropdown contains camera badge
        $this->assertStringContainsString('profile-avatar-hover-badge', $html);
    }

    public function testDashboardsDoNotContainUserManualButton(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'role'       => 'admin',
            'full_name'  => 'Admin User',
            'username'   => 'admin',
        ]);

        // Staff dashboard check
        $staffHtml = view('staff/dashboard', [
            'title'              => 'Dispatcher Dashboard',
            'announcements'      => [],
            'active_queue_count' => 0,
            'recent_departures'  => [],
            'terminals'          => [],
            'vehicleTypes'       => [],
        ]);
        $this->assertStringNotContainsString('<i class="bi bi-book"></i> User Manual', $staffHtml);

        // Admin dashboard check
        $adminHtml = view('admin/dashboard', [
            'title'              => 'Admin Dashboard',
            'stats'              => [
                'vehicles' => 0,
                'routes'   => 0,
                'users'    => 0,
                'logs'     => 0,
            ],
            'unassignedVehicles' => 0,
            'unassignedStaff'    => 0,
            'recent_logs'        => [],
            'destinations'       => [],
            'vehicleTypes'       => [],
        ]);
        $this->assertStringNotContainsString('<i class="bi bi-book"></i> User Manual', $adminHtml);
    }

    public function testAdminNavManagementAndRecordsHighlightStructure(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'role'       => 'admin',
            'full_name'  => 'Admin User',
            'username'   => 'admin',
        ]);

        $html = view('partials/nav-admin');

        // Verify Management dropdown button exists with active class capability
        $this->assertStringContainsString('Management', $html);
        $this->assertStringContainsString('dropbtn', $html);
        $this->assertStringContainsString('admin/terminals', $html);
        $this->assertStringContainsString('admin/vehicles', $html);
        $this->assertStringContainsString('admin/routes', $html);
        $this->assertStringContainsString('admin/users', $html);
        $this->assertStringContainsString('admin/departure-rules', $html);

        // Verify Records dropdown button exists with active class capability
        $this->assertStringContainsString('Records', $html);
        $this->assertStringContainsString('admin/history', $html);
        $this->assertStringContainsString('admin/logs', $html);
    }

    public function testChangePasswordViewRenders(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'role'       => 'staff',
            'full_name'  => 'Operator User',
            'username'   => 'jycgrac@gmail.com',
            'id'         => 2,
        ]);

        $html = view('auth/change_password', [
            'title'        => 'Change Password',
            'user'         => [
                'id'        => 2,
                'username'  => 'jycgrac@gmail.com',
                'full_name' => 'Operator User',
                'role'      => 'staff',
                'email'     => 'jycgrac@gmail.com',
            ],
            'email'        => 'jycgrac@gmail.com',
            'masked_email' => 'j*****c@gmail.com',
            'has_email'    => true,
        ]);

        $this->assertStringContainsString('Change Password', $html);
        $this->assertStringContainsString('Current Password', $html);
        $this->assertStringContainsString('6-Digit Email Verification Code', $html);
        $this->assertStringContainsString('Get Code', $html);
        $this->assertStringContainsString('New Password', $html);
        $this->assertStringContainsString('Confirm New Password', $html);
        $this->assertStringContainsString('Update Password', $html);
        $this->assertStringContainsString('change-password/update', $html);
        $this->assertStringContainsString('change-password/send-code', $html);
    }
}

