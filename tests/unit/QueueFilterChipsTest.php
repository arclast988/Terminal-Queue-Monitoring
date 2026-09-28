<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class QueueFilterChipsTest extends CIUnitTestCase
{
    public function testQueueFilterChipsRenderWithCounts(): void
    {
        $mockQueue = [
            [
                'id' => 1,
                'plate_number' => 'ABC123',
                'operator_name' => 'Operator 1',
                'driver_name' => 'Driver 1',
                'vehicle_type' => 'van',
                'origin' => 'Palompon',
                'destination' => 'ORMOC',
                'current_passengers' => 5,
                'capacity' => 14,
                'status' => 'waiting',
                'position' => 1,
                'estimated_departure' => null,
            ],
            [
                'id' => 2,
                'plate_number' => 'DEF456',
                'operator_name' => 'Operator 2',
                'driver_name' => 'Driver 2',
                'vehicle_type' => 'jeepney',
                'origin' => 'Palompon',
                'destination' => 'ORMOC',
                'current_passengers' => 10,
                'capacity' => 20,
                'status' => 'boarding',
                'position' => 2,
                'estimated_departure' => null,
            ],
            [
                'id' => 3,
                'plate_number' => 'GHI789',
                'operator_name' => 'Operator 3',
                'driver_name' => 'Driver 3',
                'vehicle_type' => 'minibus',
                'origin' => 'Palompon',
                'destination' => 'CALUBIAN',
                'current_passengers' => 8,
                'capacity' => 25,
                'status' => 'waiting',
                'position' => 3,
                'estimated_departure' => null,
            ],
        ];

        $html = view('public/enhanced_dashboard', [
            'title' => 'Live Terminal Monitor',
            'active_queue' => $mockQueue,
            'departure_rules' => [],
            'route_average_departures' => [],
            'routes' => [],
            'announcements' => [],
        ]);

        // Verify "All Routes" chip has total count badge (3)
        $this->assertStringContainsString('All Routes <span class="chip-count">3</span>', $html);

        // Verify ORMOC chip has count badge (2)
        $this->assertStringContainsString('ORMOC <span class="chip-count">2</span>', $html);

        // Verify CALUBIAN chip has count badge (1)
        $this->assertStringContainsString('CALUBIAN <span class="chip-count">1</span>', $html);

        // Verify CSS for filter chip count exists
        $this->assertStringContainsString('.filter-chip .chip-count', $html);
        $this->assertStringContainsString('.filter-chip.active .chip-count', $html);

        // Dynamic counts are added as text nodes so route names cannot inject markup.
        $this->assertStringContainsString("fragment.appendChild(makeQueueFilterChip('all', 'all', 'All Routes', totalCount", $html);
        $this->assertStringContainsString("badge.className = 'chip-count'", $html);
        $this->assertStringContainsString('badge.textContent = count;', $html);
    }
}
