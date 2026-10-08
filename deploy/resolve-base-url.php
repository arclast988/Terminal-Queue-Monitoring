<?php

use App\Libraries\DeploymentUrl;

require dirname(__DIR__) . '/app/Libraries/DeploymentUrl.php';

$configured = (string) (getenv('APP_BASE_URL') ?: '');
$domain = (string) (getenv('RAILWAY_PUBLIC_DOMAIN') ?: '');
if (trim($configured) !== '' && DeploymentUrl::normalize($configured) === null) {
    fwrite(STDERR, "[WARN] APP_BASE_URL is not a valid HTTP(S) site URL; using the deployment fallback.\n");
}
$resolved = DeploymentUrl::resolve($configured, $domain);
if ($resolved === 'http://localhost/' && trim($domain) === '') {
    fwrite(STDERR, "[ENV] No public domain configured; internal services will use a loopback URL.\n");
}
echo $resolved;
