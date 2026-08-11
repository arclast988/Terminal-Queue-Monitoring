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
                'user_id' => $userId,
                'action' => $action,
                'details' => $details
            ]);
        } catch (\Throwable $e) {
            // Ignore if logs table missing or any DB error
        }
    }

    /**
     * Broadcast an update to the PHP WebSocket server.
     */
    protected function broadcastUpdate(string $type, array $data = []): void
    {
        $now = microtime(true);
        // Write sync token — the polling mechanism on all clients checks this
        @file_put_contents(WRITEPATH . 'sync_token.txt', $now);

        // Invalidate the short-lived public feed caches so this change is
        // reflected on the very next poll instead of waiting out the TTL.
        // Best-effort: the short TTLs already bound any staleness if this fails.
        // Passenger changes only need the queue-status cache cleared; heavier
        // actions (add/remove/status) invalidate all cached public feeds.
        try {
            $cache = cache();
            $isPassengerChange = ($data['action'] ?? '') === 'passenger_change';
            $cache->delete('rt_queue_status');
            $cache->delete('rt_home_status');
            $cache->deleteMatching('rt_sched_status_*');

            if (! $isPassengerChange) {
                $cache->delete('rt_announcements');
                $cache->delete('rt_fares_api');
                $cache->delete('rt_fares_api_v2');
            }
        } catch (\Throwable $e) {
            // ignore — caching is an optimisation, not a correctness requirement
        }

        // Only attempt WebSocket broadcast if the server is confirmed running
        // (indicated by the presence of a PID file written by the WS server on startup)
        $pidFile = WRITEPATH . 'ws_server.pid';
        if (!file_exists($pidFile)) {
            return; // No WS server running — polling handles sync
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
            $fp = @stream_socket_client(
                'tcp://127.0.0.1:' . $broadcastPort,
                $errno,
                $errstr,
                0.2,
                STREAM_CLIENT_CONNECT
            );
            if ($fp) {
                stream_set_timeout($fp, 0, 200000);
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
     * Get a pre-configured Email service instance.
     */
    protected function getConfiguredEmailService(): \CodeIgniter\Email\Email
    {
        $config = config('Email');
        $emailSvc = \Config\Services::email();

        $fromEmail = $config->fromEmail ?: $config->SMTPUser;
        $fromName  = $config->fromName ?: 'PTTM System Feedback';

        $emailSvc->setFrom($fromEmail, $fromName);
        return $emailSvc;
    }
}
