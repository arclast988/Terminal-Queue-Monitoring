<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Links a departure rule to a route (and therefore a destination) via a
 * nullable `route_id` foreign key into `routes`. Surfaced as the
 * "Destination" column after Terminal in the admin/staff rule list.
 *
 * Idempotent: guarded with fieldExists / information_schema so it is safe
 * against the live DB and DBs seeded from jeepneynvans.sql /
 * jeepneynvans_clean.sql (which already contain departure_rules + routes).
 */
class AddRouteIdToDepartureRules extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if (! $db->fieldExists('route_id', 'departure_rules')) {
            $this->forge->addColumn('departure_rules', [
                'route_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                ],
            ]);
        }

        if (! $this->constraintExists('fk_departure_rules_route')) {
            $db->query(
                'ALTER TABLE departure_rules
                 ADD CONSTRAINT fk_departure_rules_route
                 FOREIGN KEY (route_id) REFERENCES routes(id)
                 ON DELETE SET NULL ON UPDATE CASCADE'
            );
        }
    }

    public function down()
    {
        $db = \Config\Database::connect();

        if ($this->constraintExists('fk_departure_rules_route')) {
            $db->query('ALTER TABLE departure_rules DROP CONSTRAINT fk_departure_rules_route');
        }

        if ($db->fieldExists('route_id', 'departure_rules')) {
            $this->forge->dropColumn('departure_rules', 'route_id');
        }
    }

    private function constraintExists(string $constraintName): bool
    {
        $db = \Config\Database::connect();
        $row = $db->query(
            "SELECT 1 FROM information_schema.table_constraints
             WHERE constraint_schema = current_schema()
               AND constraint_name = ?
               AND constraint_type = 'FOREIGN KEY'",
            [$constraintName]
        )->getRowArray();
        return (bool) $row;
    }
}
