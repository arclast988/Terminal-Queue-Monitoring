<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class RailwayProductionRealtimeTest extends CIUnitTestCase
{
    public function testRailwayUsesProductionWebStackAndHealthCheck(): void
    {
        $start = file_get_contents(HOMEPATH . 'start.sh');
        $railway = json_decode(file_get_contents(HOMEPATH . 'railway.json'), true, 512, JSON_THROW_ON_ERROR);
        $nixpacks = file_get_contents(HOMEPATH . 'nixpacks.toml');

        $this->assertStringContainsString('CI_ENVIRONMENT:-production', $start);
        $this->assertStringContainsString('php-fpm', $start);
        $this->assertStringContainsString("nginx -c", $start);
        $this->assertStringNotContainsString('php -S 0.0.0.0', $start);
        $this->assertSame('/healthz', $railway['deploy']['healthcheckPath']);
        $this->assertStringContainsString('aptPkgs = ["nginx"]', $nixpacks);
    }

    public function testRailwayNginxRoutesWebsocketAndPhpTraffic(): void
    {
        $nginx = file_get_contents(HOMEPATH . 'deploy/nginx.railway.conf');
        $fpm = file_get_contents(HOMEPATH . 'deploy/php-fpm.railway.conf');

        $this->assertStringContainsString('location = /ws', $nginx);
        $this->assertStringContainsString('server 127.0.0.1:__WEBSOCKET_PORT__;', $nginx);
        $this->assertStringContainsString('proxy_pass http://websocket_backend;', $nginx);
        $this->assertStringContainsString('proxy_set_header Upgrade $http_upgrade;', $nginx);
        $this->assertStringContainsString('fastcgi_param HTTPS $fastcgi_https;', $nginx);
        $this->assertStringContainsString('fastcgi_pass php_backend;', $nginx);
        $this->assertStringContainsString('pm.max_children = 6', $fpm);
    }

    public function testDeploymentDoesNotContainCredentialFallbacks(): void
    {
        $start = file_get_contents(HOMEPATH . 'start.sh');

        $this->assertStringContainsString('require_env DATABASE_URL', $start);
        $this->assertStringContainsString('require_env ENCRYPTION_KEY', $start);
        $this->assertStringContainsString('DB_PASS="${DB_CONN%%@*}"', $start);
        $this->assertStringContainsString('${EMAIL_SMTP_PASS:-}', $start);
        $this->assertStringContainsString('${EMAIL_SMTP_HOST:-smtp.gmail.com}', $start);
        $this->assertStringContainsString('${EMAIL_SMTP_PORT:-587}', $start);
        $this->assertStringContainsString('${EMAIL_SMTP_CRYPTO:-tls}', $start);
        $this->assertStringContainsString('${EMAIL_SMTP_TIMEOUT:-5}', $start);
        $this->assertStringContainsString('${EMAIL_DELIVERY_PROVIDER:-brevo}', $start);
        $this->assertStringContainsString('${BREVO_API_KEY:-}', $start);
        $this->assertStringContainsString('https://api.brevo.com/v3/smtp/email', $start);
        $this->assertStringContainsString('${EMAIL_API_TIMEOUT:-8}', $start);
        $this->assertStringContainsString('encryption.key = "$(escape_dotenv "$ENCRYPTION_KEY")"', $start);
    }

    public function testGuestBrandingUpdatesWithoutForcedReload(): void
    {
        $client = file_get_contents(FCPATH . 'js/ws-client.js');

        $this->assertStringContainsString('function applyLiveBranding(data)', $client);
        $this->assertStringNotContainsString('window.location.reload()', $client);
        $this->assertStringContainsString("document.documentElement.style.setProperty", $client);
    }

    public function testRealtimePollingUsesLowFrequencyConnectedSafetyChecks(): void
    {
        $queueSync = file_get_contents(FCPATH . 'js/queue-sync.js');

        $this->assertStringContainsString('WS_CONNECTED_HEARTBEAT_INTERVAL = 120000', $queueSync);
        $this->assertStringNotContainsString('pollingFallback: function()', $queueSync);
        $this->assertStringContainsString('if (isWSConnected)', $queueSync);
    }

    public function testRealtimeReconnectAndRefreshTrafficIsStaggered(): void
    {
        $client = file_get_contents(FCPATH . 'js/ws-client.js');
        $queueSync = file_get_contents(FCPATH . 'js/queue-sync.js');

        $this->assertStringContainsString('INITIAL_CONNECT_JITTER_MS = 750', $client);
        $this->assertStringContainsString('MAX_RETRY_JITTER_MS = 2000', $client);
        $this->assertStringContainsString('Math.random() * jitterWindow', $client);
        $this->assertStringContainsString('reconnected: _hasConnectedBefore', $client);
        $this->assertStringContainsString('CONNECTED_REFRESH_JITTER_MS = 1000', $queueSync);
        $this->assertStringContainsString('scheduleConnectedRefresh(connectionInfo)', $queueSync);
        $this->assertStringContainsString('Math.floor(CONNECTED_REFRESH_JITTER_MS / 2)', $queueSync);
    }

    public function testGuestStatusCacheCollapsesSimultaneousMisses(): void
    {
        $home = file_get_contents(APPPATH . 'Controllers/Home.php');
        $dashboard = file_get_contents(APPPATH . 'Views/public/enhanced_dashboard.php');

        $this->assertStringContainsString("cache('rt_home_status')", $home);
        $this->assertStringContainsString("fopen(WRITEPATH . 'cache/rt_home_status.lock', 'c')", $home);
        $this->assertStringContainsString('flock($lockHandle, LOCK_EX)', $home);
        $this->assertGreaterThanOrEqual(2, substr_count($home, "cache('rt_home_status')"), 'Cache must be rechecked after acquiring the lock');
        $this->assertStringContainsString('avoiding a separate duplicate request on every load', $dashboard);
        $this->assertStringNotContainsString('fetchStatus(); // Initial fetch', $dashboard);
    }

    public function testGuestShellCssIsSharedWithoutChangingPageSpecificStyles(): void
    {
        $header = file_get_contents(APPPATH . 'Views/templates/guest_header.php');
        $footer = file_get_contents(APPPATH . 'Views/templates/guestfooter.php');
        $shell = file_get_contents(FCPATH . 'assets/css/guest-shell.css');

        $this->assertStringContainsString("assets/css/guest-shell.css", $header);
        $this->assertStringNotContainsString("<style>\n", $header);
        $this->assertStringNotContainsString("<style>\n", $footer);
        $this->assertStringContainsString('.guest-header {', $shell);
        $this->assertStringContainsString('.advisory-bar {', $shell);
        $this->assertStringContainsString('.breadcrumb-section {', $shell);
        $this->assertStringContainsString("footer {", $shell);

        foreach (['schedules.php', 'fares.php', 'enhanced_dashboard.php', 'search.php'] as $view) {
            $page = file_get_contents(APPPATH . 'Views/public/' . $view);
            $this->assertStringContainsString('<style>', $page, $view . ' should retain its page-specific layout CSS.');
        }
    }
}
