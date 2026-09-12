<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSeverityToAnnouncements extends Migration
{
    public function up()
    {
        $this->forge->addColumn('announcements', [
            'severity' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'info',
                'null'       => false,
            ],
        ]);

        $this->db->query("CREATE INDEX IF NOT EXISTS idx_announcements_severity ON announcements(severity)");
    }

    public function down()
    {
        $this->db->query("DROP INDEX IF EXISTS idx_announcements_severity");
        $this->forge->dropColumn('announcements', 'severity');
    }
}
