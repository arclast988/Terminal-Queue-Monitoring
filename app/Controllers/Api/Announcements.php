<?php

namespace App\Controllers\Api;

use CodeIgniter\Controller;
use App\Models\AnnouncementModel;

/**
 * Lightweight JSON API for the announcement marquee.
 * Guest pages poll this so admin announcement changes appear without a refresh.
 * No authentication required — this is the same text already shown publicly in
 * the advisory marquee.
 */
class Announcements extends Controller
{
    public function index()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        // Cache briefly so many guests polling every 3s collapse to one DB query.
        // BaseController::broadcastUpdate() clears 'rt_announcements', so an admin
        // add/edit/delete shows up on the next poll (≤3s) instead of waiting the TTL.
        $items = cache('rt_announcements');
        if (! is_array($items)) {
            $items = [];
            try {
                $rows = (new AnnouncementModel())
                    ->select('id, message')
                    ->where('is_active', 1)
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
                foreach ($rows as $row) {
                    $items[] = [
                        'id'      => (int) $row['id'],
                        'message' => $row['message'],
                    ];
                }
            } catch (\Throwable $e) {
                // Announcements table may not exist yet — return an empty list.
                $items = [];
            }
            cache()->save('rt_announcements', $items, 300);
        }

        return $this->response
            ->setHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->setJSON([
                'success'       => true,
                'announcements' => $items,
                'ts'            => time(),
            ]);
    }
}
