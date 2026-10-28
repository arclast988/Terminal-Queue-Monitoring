<?php

/** Run startup migrations with a reliable failure exit status. */

use CodeIgniter\Boot;
use CodeIgniter\Config\DotEnv;
use Config\Paths;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit(1);
}

try {
    define('FCPATH', dirname(__DIR__) . '/public/');
    chdir(FCPATH);
    require dirname(__DIR__) . '/app/Config/Paths.php';
    $paths = new Paths();
    require $paths->systemDirectory . '/Boot.php';

    // With variables_order=GPCS, DotEnv can fill an empty $_ENV from the
    // file while preserving a different process value. Honor the explicit
    // process environment so testing cannot silently select the default DB.
    $processEnvironment = getenv('CI_ENVIRONMENT', true);
    $environment = $processEnvironment !== false && $processEnvironment !== ''
        ? $processEnvironment
        : ($_SERVER['CI_ENVIRONMENT'] ?? $_ENV['CI_ENVIRONMENT'] ?? null);
    // bootConsole expects ENVIRONMENT to be defined by its caller.
    require_once $paths->systemDirectory . '/Config/DotEnv.php';
    (new DotEnv($paths->envDirectory ?? dirname(__DIR__)))->load();
    define('ENVIRONMENT', $environment ?? $_SERVER['CI_ENVIRONMENT']
        ?? $_ENV['CI_ENVIRONMENT'] ?? (getenv('CI_ENVIRONMENT') ?: 'production'));
    Boot::bootConsole($paths);

    $runner = service('migrations');
    $runner->setNamespace(null)->setSilent(false)->clearCliMessages();
    if (!$runner->latest()) {
        throw new RuntimeException('The migration runner reported a failure.');
    }

    // latest() may report success when a migration lock prevents running, or a
    // history insert fails without DBDebug. Never start with pending migrations.
    $pending = $runner->findMigrations();
    foreach ($runner->getHistory('') as $history) {
        unset($pending[$runner->getObjectUid($history)]);
    }
    if ($pending !== []) {
        throw new RuntimeException(count($pending) . ' migration(s) are still pending.');
    }

    foreach ($runner->getCliMessages() as $message) {
        echo $message, PHP_EOL;
    }
    echo "[DB] All migrations are applied and verified.\n";
} catch (Throwable $error) {
    // Keep database connection details and SQL out of deployment output.
    if (function_exists('log_message')) {
        log_message('critical', 'Startup migration failed: {exception}', ['exception' => $error]);
    }
    fwrite(STDERR, "[DB] Migration failed or remains pending. Check writable/logs before restarting.\n");
    exit(1);
}
