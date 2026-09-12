<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class Phase1RemediationTest extends CIUnitTestCase
{
    public function testSessionDirectoryPermissionsAreSecure(): void
    {
        $sessionConfigFile = APPPATH . 'Config/Session.php';
        $this->assertFileExists($sessionConfigFile);
        $content = file_get_contents($sessionConfigFile);
        $this->assertStringContainsString('0700', $content);
        $this->assertStringNotContainsString('0777', $content);
    }

    public function testDatabaseSessionTimezoneIsConfigured(): void
    {
        $dbConfigFile = APPPATH . 'Config/Database.php';
        $this->assertFileExists($dbConfigFile);
        $content = file_get_contents($dbConfigFile);
        $this->assertStringContainsString("'options'      => '-c timezone=Asia/Manila'", $content);
    }

    public function testQueueSyncNoOnclickAttribute(): void
    {
        $jsFile = FCPATH . 'js/queue-sync.js';
        $this->assertFileExists($jsFile);
        $content = file_get_contents($jsFile);
        $this->assertStringNotContainsString("ADD_ATTR: ['onclick'", $content);
        $this->assertStringNotContainsString("ADD_ATTR: ['style', 'onclick']", $content);
    }

    public function testAdminUserValidationRequiresEightCharsPassword(): void
    {
        $controllerFile = APPPATH . 'Controllers/Admin/Users.php';
        $this->assertFileExists($controllerFile);
        $content = file_get_contents($controllerFile);
        $this->assertStringContainsString("min_length[8]", $content);
    }

    public function testAuthUpdatePasswordRequiresEightChars(): void
    {
        $controllerFile = APPPATH . 'Controllers/Auth.php';
        $this->assertFileExists($controllerFile);
        $content = file_get_contents($controllerFile);
        $this->assertStringContainsString("strlen(\$password) < 8", $content);
        $this->assertStringContainsString("Password must be at least 8 characters.", $content);
    }

    public function testUserSeederUsesTtmDomain(): void
    {
        $seederFile = APPPATH . 'Database/Seeds/UserSeeder.php';
        $this->assertFileExists($seederFile);
        $content = file_get_contents($seederFile);
        $this->assertStringContainsString('admin@ttm.local', $content);
        $this->assertStringContainsString('staff@ttm.local', $content);
        $this->assertStringContainsString('staff2@ttm.local', $content);
        $this->assertStringNotContainsString('@pttm.local', $content);
    }

    public function testVehiclesDriverNameMinLengthIsTwo(): void
    {
        $controllerFile = APPPATH . 'Controllers/Admin/Vehicles.php';
        $this->assertFileExists($controllerFile);
        $content = file_get_contents($controllerFile);
        $this->assertStringContainsString("'driver_name'", $content);
        $this->assertStringContainsString("'rules'  => 'required|min_length[2]|max_length[100]'", $content);
    }

    public function testQueueUndoCancelChecksActiveStatus(): void
    {
        $controllerFile = APPPATH . 'Controllers/Staff/Queue.php';
        $this->assertFileExists($controllerFile);
        $content = file_get_contents($controllerFile);
        $this->assertStringContainsString("(\$vehicle['status'] ?? 'active') !== 'active'", $content);
        $this->assertStringContainsString("(\$route['status'] ?? 'active') !== 'active'", $content);
    }
}
