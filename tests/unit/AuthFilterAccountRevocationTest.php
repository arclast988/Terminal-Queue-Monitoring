<?php

namespace Tests\Unit;

use App\Filters\AuthFilter;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Test\CIUnitTestCase;

final class AuthFilterAccountRevocationTest extends CIUnitTestCase
{
    public function testArchivedAccountLosesExistingAdminSession(): void
    {
        service('session')->set([
            'isLoggedIn' => true,
            'id' => 42,
            'role' => 'admin',
            'profile_image_synced_at' => time() - 61,
        ]);

        $filter = new class extends AuthFilter {
            protected function loadAccount(int $userId): ?array
            {
                return ['status' => 'archived', 'role' => 'admin', 'profile_image' => null];
            }
        };

        $response = $filter->before(service('request'), ['admin']);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringEndsWith('/login', $response->getHeaderLine('Location'));
        $this->assertFalse((bool) service('session')->get('isLoggedIn'));
    }

    public function testRoleChangeInvalidatesOldAdminSession(): void
    {
        service('session')->set([
            'isLoggedIn' => true,
            'id' => 42,
            'role' => 'admin',
            'profile_image_synced_at' => time() - 61,
        ]);

        $filter = new class extends AuthFilter {
            protected function loadAccount(int $userId): ?array
            {
                return ['status' => 'active', 'role' => 'staff', 'profile_image' => null];
            }
        };

        $response = $filter->before(service('request'), ['admin']);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertStringEndsWith('/login', $response->getHeaderLine('Location'));
        $this->assertFalse((bool) service('session')->get('isLoggedIn'));
    }
}
