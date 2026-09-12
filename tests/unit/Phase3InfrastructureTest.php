<?php

use CodeIgniter\Test\CIUnitTestCase;
use App\Commands\MaintenancePurge;

/**
 * @internal
 */
final class Phase3InfrastructureTest extends CIUnitTestCase
{
    public function testDeployScriptDoesNotExposePort8081Publicly(): void
    {
        $deployScript = HOMEPATH . 'ubuntu_migration/deploy.sh';
        $this->assertFileExists($deployScript);
        $content = file_get_contents($deployScript);

        // Ensure 8081 is not directly opened via UFW
        $this->assertStringNotContainsString('ufw allow 8081', $content);
        $this->assertStringContainsString("ufw allow 'Nginx Full'", $content);
    }

    public function testInstallLinuxScriptDefaultsToProductionEnvironment(): void
    {
        $installScript = HOMEPATH . 'install_linux.sh';
        $this->assertFileExists($installScript);
        $content = file_get_contents($installScript);

        $this->assertStringContainsString('CI_ENVIRONMENT = production', $content);
    }

    public function testWebsocketServiceHasRobustRestartPolicy(): void
    {
        $serviceFile = HOMEPATH . 'ubuntu_migration/jeepney-websocket.service';
        $this->assertFileExists($serviceFile);
        $content = file_get_contents($serviceFile);

        $this->assertStringContainsString('Restart=always', $content);
        $this->assertStringContainsString('RestartSec=5s', $content);
        $this->assertStringContainsString('Environment=websocket.bindAddress=127.0.0.1', $content);
    }

    public function testMaintenancePurgeCommandIsDefined(): void
    {
        $cmdFile = APPPATH . 'Commands/MaintenancePurge.php';
        $this->assertFileExists($cmdFile);

        $cmd = new MaintenancePurge(service('logger'), service('commands'));
        $this->assertInstanceOf(MaintenancePurge::class, $cmd);
    }
}
