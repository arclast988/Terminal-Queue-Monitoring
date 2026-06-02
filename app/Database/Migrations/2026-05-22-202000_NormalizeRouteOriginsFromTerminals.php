<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class NormalizeRouteOriginsFromTerminals extends Migration
{
    public function up()
    {
        $db = \Config\Database::connect();

        if (!$db->tableExists('routes') || !$db->tableExists('terminals')) {
            return;
        }

        if (!$db->fieldExists('origin', 'routes') || !$db->fieldExists('terminal_id', 'routes')) {
            return;
        }

        $db->query("
            UPDATE routes
            JOIN terminals ON terminals.id = routes.terminal_id
            SET routes.origin = UPPER(TRIM(terminals.name))
            WHERE routes.origin <> UPPER(TRIM(terminals.name))
        ");
    }

    public function down()
    {
        // Intentionally irreversible: this only normalizes derived route origins
        // to the linked terminal name and does not remove any schema.
    }
}
