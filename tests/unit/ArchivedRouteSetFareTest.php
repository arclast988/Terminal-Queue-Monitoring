<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class ArchivedRouteSetFareTest extends CIUnitTestCase
{
    public function testArchivedRouteDoesNotRenderSetFareButton(): void
    {
        helper(['url', 'html', 'form', 'vehicle', 'fare']);

        // Mock session with admin role
        $session = service('session');
        $session->set([
            'isLoggedIn' => true,
            'role'       => 'admin',
            'username'   => 'testadmin',
        ]);

        $groupedRoutes = [
            '1_ORMOC' => [
                'terminal_name' => 'PALOMPON',
                'destination'   => 'ORMOC',
                'status'        => 'archived',
                'items'         => [
                    [
                        'id'           => 10,
                        'terminal_id'  => 1,
                        'terminal_name'=> 'PALOMPON',
                        'destination'  => 'ORMOC',
                        'vehicle_type' => 'van',
                        'status'       => 'archived',
                        'fare'         => 0,
                    ],
                    [
                        'id'           => 11,
                        'terminal_id'  => 1,
                        'terminal_name'=> 'PALOMPON',
                        'destination'  => 'ORMOC',
                        'vehicle_type' => 'minibus',
                        'status'       => 'archived',
                        'fare'         => 0,
                    ],
                    [
                        'id'           => 12,
                        'terminal_id'  => 1,
                        'terminal_name'=> 'PALOMPON',
                        'destination'  => 'ORMOC',
                        'vehicle_type' => 'jeepney',
                        'status'       => 'archived',
                        'fare'         => 0,
                    ],
                ],
            ],
            '1_TACLOBAN' => [
                'terminal_name' => 'PALOMPON',
                'destination'   => 'TACLOBAN',
                'status'        => 'active',
                'items'         => [
                    [
                        'id'           => 20,
                        'terminal_id'  => 1,
                        'terminal_name'=> 'PALOMPON',
                        'destination'  => 'TACLOBAN',
                        'vehicle_type' => 'van',
                        'status'       => 'active',
                        'fare'         => 0,
                    ],
                    [
                        'id'           => 21,
                        'terminal_id'  => 1,
                        'terminal_name'=> 'PALOMPON',
                        'destination'  => 'TACLOBAN',
                        'vehicle_type' => 'bus',
                        'status'       => 'active',
                        'fare'         => 250.00,
                    ],
                ],
            ],
        ];

        $html = view('admin/routes/index', [
            'title'               => 'Manage Routes',
            'routes'              => [],
            'groupedRoutes'       => $groupedRoutes,
            'countActiveGroups'   => 1,
            'countArchivedGroups' => 1,
        ]);

        // 1. The archived route (ORMOC) card must be present with archived badge
        $this->assertStringContainsString('PALOMPON', $html);
        $this->assertStringContainsString('ORMOC', $html);
        $this->assertStringContainsString('route-card-archived', $html);

        // 2. The active route (TACLOBAN) has one item with no fare -> "Set Fare" must be present for active route
        $this->assertStringContainsString('fares?action=add&terminal_id=1&destination=TACLOBAN&vehicle_type=van', $html);
        $this->assertStringContainsString('Set Fare', $html);

        // 3. For the archived route items (ORMOC), "Set Fare" link must NOT be present
        $this->assertStringNotContainsString('destination=ORMOC', $html);

        // 4. Muted dash must be rendered for the archived route items with no fare
        $this->assertStringContainsString('&mdash;', $html);

        // 5. Active route with fare (250) must display formatted fare
        $this->assertStringContainsString('₱250.00', $html);
    }
}
