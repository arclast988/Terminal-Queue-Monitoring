<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\LogModel;

/**
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 *
 * Extend this class in any new controllers:
 * ```
 *     class Home extends BaseController
 * ```
 *
 * For security, be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */

    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Load here all helpers you want to be available in your controllers that extend BaseController.
        // Caution: Do not put the this below the parent::initController() call below.
        // $this->helpers = ['form', 'url'];

        // Caution: Do not edit this line.
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        // $this->session = service('session');
    }

    /**
     * Log an activity for the System Activity Log (admin). Safe if logs table missing.
     * Also opportunistically enforces 60-day log retention policy.
     */
    protected function logActivity(string $action, string $details): void
    {
        $userId = session()->get('id');
        if (!$userId && $action !== 'Login') {
            return;
        }
        try {
            $logModel = new LogModel();
            $logModel->insert([
                'user_id'   => $userId,
                'action'    => $action,
                'details'   => $details,
                'timestamp' => date('Y-m-d H:i:s')
            ]);

            // Opportunistically prune logs older than 60 days (throttled to run at most every 6 hours)
            if (!cache('rt_logs_last_purged')) {
                $logModel->purgeOldLogs(60);
                cache()->save('rt_logs_last_purged', time(), 21600);
            }
        } catch (\Throwable $e) {
            // Ignore if logs table missing or any DB error
        }
    }

    /**
     * Broadcast an update to the PHP WebSocket server.
     */
    protected function broadcastUpdate(string $type, array $data = []): void
    {
        $isPassengerChange = ($data['action'] ?? '') === 'passenger_change';
        // Passenger taps fire very frequently: they are patched in-place via
        // WS/passenger_change + poll counts, so they must NOT bump the global
        // sync token (otherwise every tap would trigger a full list refresh).
        // Structural changes (vehicle add/edit, queue add/status, etc.) bump
        // the token so pollers can detect modal-list changes without WS.
        $now = microtime(true);
        if (! $isPassengerChange) {
            // Atomic sync-token write to avoid partial reads under concurrency.
            $tokenFile = WRITEPATH . 'sync_token.txt';
            $tmpFile   = $tokenFile . '.' . getmypid() . '.tmp';
            if (@file_put_contents($tmpFile, (string) $now, LOCK_EX) !== false) {
                @rename($tmpFile, $tokenFile);
            } else {
                @file_put_contents($tokenFile, (string) $now, LOCK_EX);
            }
        }

        // Invalidate the short-lived public feed caches so this change is
        // reflected on the very next poll instead of waiting out the TTL.
        // Best-effort: the short TTLs already bound any staleness if this fails.
        // Passenger changes only need the queue-status cache cleared; heavier
        // actions (add/remove/status) invalidate all cached public feeds.
        try {
            $cache = cache();
            $cache->delete('rt_queue_status');
            $cache->delete('rt_queue_status_pkg');
            $cache->delete('rt_home_status');
            // Schedules display passenger counts, so passenger updates MUST invalidate schedules cache
            $cache->delete('rt_sched_status_cbe5cfdf7c2118a9c3d78ef1d684f3afa089201352886449a06a6511cfef74a7');
            try {
                $cache->deleteMatching('rt_sched_status_*');
            } catch (\Throwable $e) {
                // FileHandler glob scan fallback
            }

            if (! $isPassengerChange) {
                $cache->delete('rt_announcements');
                $cache->delete('rt_announcements_pkg');
                $cache->delete('rt_fares_api');
                $cache->delete('rt_fares_api_v2');
                $cache->delete('db_vehicle_types');
            }
        } catch (\Throwable $e) {
            // ignore — caching is an optimisation, not a correctness requirement
        }

        $msgId = bin2hex(random_bytes(4));
        $payload = json_encode([
            'broadcast_id' => $msgId,
            'sync_token'   => $now,
            'type'         => $type,
            'data'         => $data,
            'timestamp'    => date('Y-m-d H:i:s'),
        ]);

        try {
            $broadcastPort = (int) env('websocket.broadcastPort', 8082);
            // Resilient loopback connect with 200ms timeout
            $fp = @stream_socket_client(
                'tcp://127.0.0.1:' . $broadcastPort,
                $errno,
                $errstr,
                0.20,
                STREAM_CLIENT_CONNECT
            );
            if (! $fp) {
                // Retry once on transient busy loopback
                usleep(10000);
                $fp = @stream_socket_client(
                    'tcp://127.0.0.1:' . $broadcastPort,
                    $errno,
                    $errstr,
                    0.20,
                    STREAM_CLIENT_CONNECT
                );
            }
            if ($fp) {
                stream_set_timeout($fp, 0, 100000);
                @fwrite($fp, $payload);
                @fclose($fp);
            }
        } catch (\Throwable $e) {
            // Silent fail — polling fallback is always active
        }
    }

    /**
     * Get all active announcements ordered by sort_order.
     */
    protected function getActiveAnnouncements(): array
    {
        $announcementModel = new \App\Models\AnnouncementModel();
        return $announcementModel->where('is_active', 1)->orderBy('sort_order', 'ASC')->findAll();
    }

    /**
     * Compare submitted scalar fields against the stored record.
     * Values are string-normalized; arrays are compared order-insensitively.
     * Returns true when every key in $new matches $old (missing = '').
     */
    protected function inputsUnchanged(array $old, array $new): bool
    {
        foreach ($new as $key => $value) {
            if (is_array($value)) {
                $oldValues = array_map('strval', (array) ($old[$key] ?? []));
                $newValues = array_map('strval', $value);
                sort($oldValues);
                sort($newValues);
                if ($oldValues !== $newValues) {
                    return false;
                }
                continue;
            }
            if ((string) ($old[$key] ?? '') !== (string) $value) {
                return false;
            }
        }
        return true;
    }

    /**
     * Redirect back with an informational notice when an update form was
     * submitted without any actual changes (no write, log, or broadcast).
     */
    protected function noChangesResponse()
    {
        return redirect()->back()->withInput()->with('info', 'No changes detected — nothing was updated.');
    }

    /**
     * Get a pre-configured Email service instance.
     */    protected function getConfiguredEmailService(): \CodeIgniter\Email\Email
    {
        $config = config('Email');
        $emailSvc = \Config\Services::email();

        $fromEmail = $config->fromEmail ?: $config->SMTPUser;
        $fromName  = $config->fromName ?: (app_name() . ' System');

        $emailSvc->setFrom($fromEmail, $fromName);
        return $emailSvc;
    }
}
