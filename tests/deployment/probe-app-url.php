<?php

// Bootstrap the real production configuration without reading a local .env or
// connecting to a database. The CLI and web requests share this URL validator.
$root = dirname(__DIR__, 2);
$input = json_decode($argv[1], true, 512, JSON_THROW_ON_ERROR);
foreach ([
    'CI_ENVIRONMENT' => 'production',
    'app.baseURL' => $input['configured'],
    'RAILWAY_PUBLIC_DOMAIN' => $input['domain'],
    'database.default.hostname' => '127.0.0.1',
    'database.default.port' => '1',
    'database.default.database' => 'startup_probe',
    'database.default.username' => 'startup_probe',
    'database.default.password' => 'unused',
] as $key => $value) {
    $_ENV[$key] = $_SERVER[$key] = $value;
    putenv($key . '=' . $value);
}
$_SERVER['HTTP_HOST'] = $input['host'];
$_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
$_SERVER['argv'] = [$root . '/spark', 'list'];
$_SERVER['argc'] = 2;
define('FCPATH', $root . '/public/');
chdir(FCPATH);
require $root . '/app/Config/Paths.php';
$paths = new Config\Paths();
$paths->envDirectory = __DIR__;
$paths->writableDirectory = $root . '/build/deployment-url-probe';
foreach (['', '/logs', '/cache', '/session'] as $dir) {
    if (!is_dir($paths->writableDirectory . $dir)) {
        mkdir($paths->writableDirectory . $dir, 0777, true);
    }
}
require $paths->systemDirectory . '/Boot.php';
ob_start();
$code = CodeIgniter\Boot::bootSpark($paths);
ob_end_clean();
if ($code !== 0) {
    exit($code);
}
$config = config('App');
$uri = new CodeIgniter\HTTP\SiteURI($config);
echo "\nPROBE_URL:" . json_encode(['baseURL' => $config->baseURL, 'uri' => $uri->getBaseURL()], JSON_THROW_ON_ERROR);
