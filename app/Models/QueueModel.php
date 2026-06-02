<?php

namespace App\Models;

use CodeIgniter\Model;

class QueueModel extends Model
{
    protected $table            = 'queue';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['vehicle_id', 'route_id', 'status', 'current_passengers', 'position', 'arrival_time', 'estimated_departure', 'departure_time'];

    // Dates
    protected $useTimestamps = false; // Manually handling arrival_time and departure_time

    /**
     * Renumber the active queue's `position` so it reflects DEPARTURE order:
     *   1. Boarding vehicles first, soonest estimated_departure first.
     *   2. Then waiting vehicles (no countdown yet) in arrival order.
     *
     * Keeping `position` in departure order means every existing
     * `ORDER BY queue.position` query (staff queue, dashboard, schedules, the
     * JSON feeds) shows the right order and the #position badges stay correct.
     * Call this inside a transaction after add()/status changes.
     */
    public function reorderByDeparture(): void
    {
        $active = $this->whereIn('status', ['waiting', 'boarding'])->findAll();

        // Sort in PHP (the active queue is small) so the ordering is fully
        // deterministic: boarding vehicles (have an ETA) first by soonest ETA,
        // then waiting vehicles (null ETA) by arrival order.
        usort($active, static function ($a, $b) {
            $aNull = empty($a['estimated_departure']);
            $bNull = empty($b['estimated_departure']);
            if ($aNull !== $bNull) {
                return $aNull <=> $bNull;            // non-null (boarding) before null (waiting)
            }
            if (! $aNull) {                          // both boarding: soonest departure first
                $cmp = strcmp((string) $a['estimated_departure'], (string) $b['estimated_departure']);
                if ($cmp !== 0) {
                    return $cmp;
                }
            }
            $cmp = strcmp((string) $a['arrival_time'], (string) $b['arrival_time']); // FCFS
            return $cmp !== 0 ? $cmp : ((int) $a['id'] <=> (int) $b['id']);
        });

        $pos = 1;
        foreach ($active as $row) {
            if ((int) $row['position'] !== $pos) {
                $this->update($row['id'], ['position' => $pos]);
            }
            $pos++;
        }
    }
}
