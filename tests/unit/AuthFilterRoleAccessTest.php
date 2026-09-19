<?php

namespace Tests\Unit;

use App\Filters\AuthFilter;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Test\CIUnitTestCase;

final class AuthFilterRoleAccessTest extends CIUnitTestCase
{
    public function testSuperAdminCannotAccessStaffOnlyRoutes(): void
    {
        service('session')->set([
            'isLoggedIn' => true,
            'role' => 'super_admin',
        ]);

        $result = (new AuthFilter())->before(service('request'), ['staff']);

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

        $this->assertNull((new AuthFilter())->before(service('request'), ['admin']));
        $this->assertNull((new AuthFilter())->before(service('request'), ['admin', 'staff']));
    }

    public function testDispatcherRetainsStaffOnlyAccess(): void
    {
        service('session')->set([
            'isLoggedIn' => true,
            'role' => 'staff',
        ]);

        $this->assertNull((new AuthFilter())->before(service('request'), ['staff']));
    }

    public function testAdminRemainsBlockedFromStaffOnlyRoutes(): void
    {
        service('session')->set([
            'isLoggedIn' => true,
            'role' => 'admin',
        ]);

        $this->assertInstanceOf(
            RedirectResponse::class,
            (new AuthFilter())->before(service('request'), ['staff'])
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
