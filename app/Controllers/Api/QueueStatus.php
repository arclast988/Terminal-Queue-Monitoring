<?php

namespace App\Controllers\Api;

use CodeIgniter\Controller;
use App\Models\QueueModel;

/**
 * Lightweight JSON API for queue sync polling.
 * Returns only active queue items with passenger counts.
 * No authentication required (read-only public data).
 */
class QueueStatus extends Controller
{
    public function index()
    {
        // Read-only poll endpoint hit every few seconds: release session
        // lock immediately so concurrent polls don't serialize.
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        // Read sync token first to ensure absolute freshness
        $syncToken = '';
        $syncTokenTime = 0.0;
        try {
            $tokenFile = WRITEPATH . 'sync_token.txt';
            if (is_file($tokenFile)) {
                $syncToken = trim((string) @file_get_contents($tokenFile));
                $syncTokenTime = (float) $syncToken;
            }
        } catch (\Throwable $e) {
            $syncToken = '';
        }

        $cached = cache('rt_queue_status_pkg');
        if (is_array($cached) && isset($cached['cached_at']) && $syncTokenTime > 0 && $cached['cached_at'] < $syncTokenTime) {
            $cached = null;
        }

        if (! is_array($cached)) {
            $queueModel = new QueueModel();

            $queue = $queueModel->select('queue.id, queue.position, queue.status, queue.current_passengers, vehicles.capacity, vehicles.plate_number, vehicles.type as vehicle_type, vehicles.driver_name, queue.estimated_departure, terminals.name as origin, routes.destination')
                ->withFullJoins()
                ->whereIn('queue.status', ['waiting', 'boarding'])
                ->orderBy('routes.destination', 'ASC')
                ->orderBy("CASE WHEN queue.status = 'boarding' THEN 0 ELSE 1 END", 'ASC')
                ->orderBy('queue.position', 'ASC')
                ->findAll();

            // Build a simple lookup by queue ID
            $items = [];
            foreach ($queue as $item) {
                $items[] = [
                    'id'                 => (int)$item['id'],
                    'position'           => (int)$item['position'],
                    'status'             => $item['status'],
                    'current_passengers' => (int)$item['current_passengers'],
                    'capacity'           => (int)$item['capacity'],
                    'plate_number'       => $item['plate_number'],
                    'vehicle_type'       => $item['vehicle_type'] ?? null,
                    'driver_name'        => $item['driver_name'] ?? null,
                    'estimated_departure'=> $item['estimated_departure'] ?? null,
                    'origin'             => $item['origin'],
                    'destination'        => $item['destination'],
                ];
            }

            $cached = [
                'items'     => $items,
                'cached_at' => microtime(true),
            ];

            cache()->save('rt_queue_status_pkg', $cached, 2);
            cache()->save('rt_queue_status', $items, 2);
        } else {
            $items = $cached['items'];
        }

        return $this->response
            ->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->setJSON([
                'success' => true,
                'queue'   => $items,
                'vehicle_type_colors' => get_db_vehicle_types(),
                'sync_token' => $syncToken,
                'queue_hash' => md5(json_encode($items)),
                'ts'      => time()
            ]);
    }
    public function checkAvailability($vehicleId)
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        $queueModel = new \App\Models\QueueModel();

        // 1. Check if already in active queue
        $existing = $queueModel->where('vehicle_id', $vehicleId)
            ->whereIn('status', ['waiting', 'boarding'])
            ->first();

        if ($existing) {
            return $this->response->setJSON([
                'success'   => true,
                'available' => false,
                'reason'    => 'already_queued',
                'message'   => 'This vehicle is already in the queue.'
            ]);
        }

        // 2. Check for recent departure (within the 30-minute cooldown)
        $thirtyMinutesAgo = date('Y-m-d H:i:s', strtotime('-30 minutes'));
        $recentDeparture = $queueModel->where('vehicle_id', $vehicleId)
            ->where('status', 'departed')
            ->where('departure_time >=', $thirtyMinutesAgo)
            ->orderBy('departure_time', 'DESC')
            ->first();

        if ($recentDeparture) {
            $departTime = strtotime($recentDeparture['departure_time']);
            $secondsAgo = time() - $departTime;
            $waitRemaining = max(0, (30 * 60) - $secondsAgo);
            $waitMinutes = (int) ceil($waitRemaining / 60);
            
            return $this->response->setJSON([
                'success'   => true,
                'available' => false,
                'reason'    => 'recently_departed',
                'message'   => "This vehicle departed recently. Please wait about {$waitMinutes} more minute(s).",
                'departure_time' => $recentDeparture['departure_time']
            ]);
        }

        return $this->response->setJSON([
            'success'   => true,
            'available' => true,
            'message'   => 'Vehicle is available for queueing.'
        ]);
    }
}
