<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class StartupMigrationFailureTest extends TestCase
{
    private string $fixture;

    protected function setUp(): void
    {
        $this->fixture = sys_get_temp_dir() . '/tq-startup-' . bin2hex(random_bytes(6));
        foreach (['deploy', 'public', 'app/Config', 'system/Config'] as $directory) {
            mkdir($this->fixture . '/' . $directory, 0700, true);
        }
        copy(dirname(__DIR__, 2) . '/deploy/migrate.php', $this->fixture . '/deploy/migrate.php');
        file_put_contents($this->fixture . '/app/Config/Paths.php', <<<'FIXTURE'
<?php
namespace Config;
class Paths { public string $systemDirectory = __DIR__ . '/../../system'; }
FIXTURE);
        file_put_contents($this->fixture . '/system/Config/DotEnv.php', <<<'FIXTURE'
<?php
namespace CodeIgniter\Config;
class DotEnv {
    public function __construct($path) {}
    public function load() {
        $file = dirname(__DIR__, 2) . '/dotenv-environment';
        if (is_file($file)) $_ENV['CI_ENVIRONMENT'] = trim(file_get_contents($file));
    }
}
FIXTURE);
        file_put_contents($this->fixture . '/system/Boot.php', <<<'FIXTURE'
<?php
namespace CodeIgniter {
    class Boot {
        public static function bootConsole($paths): void {
            file_put_contents(__DIR__ . '/../environment-seen', ENVIRONMENT);
            file_put_contents(__DIR__ . '/../dotenv-seen', $_ENV['CI_ENVIRONMENT'] ?? '');
        }
    }
}
namespace {
    function service($name) { return new StartupRunnerFixture(); }
    function log_message($level, $message, $context) {}
    class StartupRunnerFixture {
        private function mode() { return trim(file_get_contents(__DIR__ . '/../scenario')); }
        public function setNamespace($namespace) {
            if ($namespace !== null) throw new \RuntimeException('Must include all namespaces');
            return $this;
        }
        public function setSilent($silent) {
            if ($silent) throw new \RuntimeException('Must propagate failures');
            return $this;
        }
        public function clearCliMessages() { return $this; }
        public function latest() {
            file_put_contents(__DIR__ . '/../latest-called', 'yes');
            if ($this->mode() === 'throw') throw new \RuntimeException('secret-connection-password');
            return $this->mode() !== 'false';
        }
        public function findMigrations() {
            return ['1Migration' => (object) ['version' => '1', 'class' => 'Migration']];
        }
        public function getHistory($group) {
            if ($group !== '') throw new \RuntimeException('Must verify all database groups');
            file_put_contents(__DIR__ . '/../history-checked', 'yes');
            return $this->mode() === 'pending' ? [] : [(object) ['version' => '1', 'class' => 'Migration']];
        }
        public function getObjectUid($migration) { return $migration->version . $migration->class; }
        public function getCliMessages() { return []; }
    }
}
FIXTURE);
    }

    protected function tearDown(): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->fixture, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->fixture);
    }

    public static function runnerScenarios(): array
    {
        return [
            'applied migrations' => ['success', 0],
            'runner returns false' => ['false', 1],
            'runner throws' => ['throw', 1],
            'runner reports success but history is pending' => ['pending', 1],
        ];
    }

    #[DataProvider('runnerScenarios')]
    public function testDeploymentRunnerUsesResultExceptionsAndHistory(string $scenario, int $expectedExit): void
    {
        file_put_contents($this->fixture . '/scenario', $scenario);
        [$exit, $output] = $this->runProcess([PHP_BINARY, $this->fixture . '/deploy/migrate.php']);

        $this->assertSame($expectedExit, $exit, $output);
        $this->assertFileExists($this->fixture . '/latest-called');
        $this->assertStringNotContainsString('secret-connection-password', $output);
        if ($expectedExit === 0) {
            $this->assertFileExists($this->fixture . '/history-checked');
            $this->assertStringContainsString('All migrations are applied and verified', $output);
        } else {
            $this->assertStringContainsString('Migration failed or remains pending', $output);
            $this->assertStringNotContainsString('All migrations are applied and verified', $output);
        }
    }

    public static function startupScenarios(): array
    {
        return [
            'already initialized and all migrated' => [0, 0, 0, 'SERVICES_ALLOWED'],
            'schema initialization fails' => [1, 0, 1, 'Database initialization failed'],
            'migration process fails' => [0, 7, 1, 'Database migrations failed'],
        ];
    }

    public function testProcessEnvironmentOverridesConflictingDotEnvWithDefaultPhpVariableOrder(): void
    {
        file_put_contents($this->fixture . '/scenario', 'success');
        file_put_contents($this->fixture . '/dotenv-environment', 'development');
        [$exit, $output] = $this->runProcess(
            [PHP_BINARY, '-d', 'variables_order=GPCS', $this->fixture . '/deploy/migrate.php'],
            ['CI_ENVIRONMENT' => 'testing']
        );

        $this->assertSame(0, $exit, $output);
        $this->assertSame('development', file_get_contents($this->fixture . '/dotenv-seen'));
        $this->assertSame('testing', file_get_contents($this->fixture . '/environment-seen'));
        $this->assertFileExists($this->fixture . '/history-checked');
    }

    #[DataProvider('startupScenarios')]
    public function testStartupStopsBeforeServicesWhenDatabasePreparationFails(
        int $importExit, int $migrationExit, int $expectedExit, string $expectedOutput
    ): void {
        $start = file_get_contents(dirname(__DIR__, 2) . '/start.sh');
        $begin = strpos($start, 'echo "[DB] Checking schema and migrations..."');
        $end = strpos($start, 'PHP_FPM_BIN=', $begin);
        $this->assertNotFalse($begin);
        $this->assertNotFalse($end);
        $block = substr($start, $begin, $end - $begin);
        $harness = 'set -Eeuo pipefail' . "\nAPP_ROOT=" . escapeshellarg($this->fixture) . "\n"
            . 'php() { case "$1" in */import_schema.php) echo import >> "$APP_ROOT/calls"; return '
            . $importExit . ' ;; */deploy/migrate.php) echo migrate >> "$APP_ROOT/calls"; return '
            . $migrationExit . ' ;; *) return 99 ;; esac; }' . "\n"
            . $block . "\necho SERVICES_ALLOWED\n";
        file_put_contents($this->fixture . '/startup.sh', $harness);
        [$exit, $output] = $this->runProcess(['/bin/bash', $this->fixture . '/startup.sh']);

        $this->assertSame($expectedExit, $exit, $output);
        $this->assertStringContainsString($expectedOutput, $output);
        $this->assertSame($importExit === 0 ? "import\nmigrate\n" : "import\n", file_get_contents($this->fixture . '/calls'));
        if ($expectedExit !== 0) {
            $this->assertStringNotContainsString('SERVICES_ALLOWED', $output);
        }
    }

    private function runProcess(array $command, array $environment = []): array
    {
        $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['redirect', 1]], $pipes, $this->fixture,
            array_replace(getenv(), ['RUN_DEFAULT_SEEDERS' => 'false'], $environment));
        $this->assertIsResource($process);
        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        return [proc_close($process), $output];
    }
}
