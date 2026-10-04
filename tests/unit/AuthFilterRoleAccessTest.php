<?php

namespace Tests\Unit;

use App\Filters\AuthFilter;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Test\CIUnitTestCase;

class RoleAccessAuthFilter extends AuthFilter
{
    protected function loadAccount(int $userId): ?array
    {
        return ['status' => 'active', 'role' => session()->get('role'), 'password_hash' => 'test-password-hash'];
    }
}

final class AuthFilterRoleAccessTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        service('session')->set(['id' => 42, 'auth_password_fingerprint' => hash('sha256', 'test-password-hash')]);
    }

    public function testSuperAdminCannotAccessStaffOnlyRoutes(): void
    {
        service('session')->set([
            'isLoggedIn' => true,
            'role' => 'super_admin',
        ]);

        $result = (new RoleAccessAuthFilter())->before(service('request'), ['staff']);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertStringEndsWith('/admin/dashboard', $result->getHeaderLine('Location'));
        $this->assertSame('Dispatcher-only pages are restricted to dispatchers.', service('session')->getFlashdata('error'));
    }

    public function testSuperAdminStillInheritsAdminAccess(): void
    {
        service('session')->set([
            'isLoggedIn' => true,
            'role' => 'super_admin',
        ]);

        $this->assertNull((new RoleAccessAuthFilter())->before(service('request'), ['admin']));
        $this->assertNull((new RoleAccessAuthFilter())->before(service('request'), ['admin', 'staff']));
    }

    public function testDispatcherRetainsStaffOnlyAccess(): void
    {
        service('session')->set([
            'isLoggedIn' => true,
            'role' => 'staff',
        ]);

        $this->assertNull((new RoleAccessAuthFilter())->before(service('request'), ['staff']));
    }

    public function testAdminRemainsBlockedFromStaffOnlyRoutes(): void
    {
        service('session')->set([
            'isLoggedIn' => true,
            'role' => 'admin',
        ]);

        $this->assertInstanceOf(
            RedirectResponse::class,
            (new RoleAccessAuthFilter())->before(service('request'), ['staff'])
        );
    }

    public function testQueueRoutesRemainInsideStaffOnlyGroup(): void
    {
        $routes = file_get_contents(APPPATH . 'Config/Routes.php');
        $staffGroupStart = strpos($routes, '$routes->group(\'staff\', [\'filter\' => \'auth:staff\']');

        $this->assertNotFalse($staffGroupStart);
        $staffGroup = substr($routes, $staffGroupStart);
        $this->assertStringContainsString('$routes->get(\'queue\', \'Staff\\Queue::index\')', $staffGroup);
        $this->assertStringContainsString('$routes->post(\'queue/setPassengers/(:num)\', \'Staff\\Queue::setPassengers/$1\')', $staffGroup);
        $this->assertStringContainsString('$routes->post(\'queue/updateDriver/(:num)\', \'Staff\\Queue::updateDriver/$1\')', $staffGroup);
    }
}
