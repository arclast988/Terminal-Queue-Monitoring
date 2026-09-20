<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

final class SuperAdminIntegrityTest extends CIUnitTestCase
{
    public function testProductionStartupDoesNotRecreateDemoAccountsByDefault(): void
    {
        $start = file_get_contents(HOMEPATH . 'start.sh');

        $this->assertStringContainsString('RUN_DEFAULT_SEEDERS:-false', $start);
        $this->assertStringContainsString('Default demo users skipped', $start);
    }

    public function testSeederNeverDeletesOrResetsExistingUsers(): void
    {
        $seeder = file_get_contents(APPPATH . 'Database/Seeds/UserSeeder.php');

        $this->assertStringNotContainsString('->delete()', $seeder);
        $this->assertStringContainsString('Seed only missing demo accounts', $seeder);
        $this->assertStringContainsString("\$hasSuperAdmin ? 'admin' : 'super_admin'", $seeder);
    }

    public function testUserEditorCannotPromoteASecondSuperAdmin(): void
    {
        $controller = file_get_contents(APPPATH . 'Controllers/Admin/Users.php');

        $this->assertStringContainsString("\$role === 'super_admin' && \$targetUser['role'] !== 'super_admin'", $controller);
        $this->assertStringContainsString('Only one super admin is allowed.', $controller);
    }

    public function testLegacyDuplicateCleanupMigrationIsAvailable(): void
    {
        $migration = APPPATH . 'Database/Migrations/2026-09-21-060000_NormalizeLegacySuperAdmins.php';

        $this->assertFileExists($migration);
        $source = file_get_contents($migration);
        $this->assertStringContainsString("['@tlm.local', '@ttm.local']", $source);
        $this->assertStringContainsString("'role' => 'admin'", $source);
        $this->assertStringContainsString("'status' => 'archived'", $source);
    }
}
