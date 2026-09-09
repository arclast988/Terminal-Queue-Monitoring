<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class LogoutConfirmationTest extends CIUnitTestCase
{
    public function testAdminNavHasLogoutModalTrigger(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'role'       => 'super_admin',
            'full_name'  => 'System Administrator',
            'username'   => 'admin',
        ]);

        $html = view('partials/header');

        $this->assertStringContainsString('logoutModal', $html);
        $this->assertStringContainsString('confirmLogout(event)', $html);
        $this->assertStringContainsString('data-bs-target="#logoutModal"', $html);
        $this->assertStringContainsString('System Administrator', $html);
        $this->assertStringContainsString('Super Admin', $html);
        $this->assertStringContainsString('Confirm Logout', $html);
        $this->assertStringContainsString('Stay Signed In', $html);
    }

    public function testDispatcherNavHasLogoutModalTrigger(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'role'       => 'staff',
            'full_name'  => 'Rouge Omde',
            'username'   => 'staff',
        ]);

        $html = view('partials/header');

        $this->assertStringContainsString('logoutModal', $html);
        $this->assertStringContainsString('confirmLogout(event)', $html);
        $this->assertStringContainsString('data-bs-target="#logoutModal"', $html);
        $this->assertStringContainsString('Rouge Omde', $html);
        $this->assertStringContainsString('Dispatcher', $html);
        $this->assertStringContainsString('Confirm Logout', $html);
        $this->assertStringContainsString('Stay Signed In', $html);
    }

    public function testGuestHeaderHasLogoutModalForLoggedInUser(): void
    {
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'role'       => 'staff',
            'full_name'  => 'Rouge Omde',
            'username'   => 'staff',
        ]);

        $html = view('templates/guest_header');

        $this->assertStringContainsString('logoutModal', $html);
        $this->assertStringContainsString('confirmLogout(event)', $html);
        $this->assertStringContainsString('data-bs-target="#logoutModal"', $html);
    }
}
