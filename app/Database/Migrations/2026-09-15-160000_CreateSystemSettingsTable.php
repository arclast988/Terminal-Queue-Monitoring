<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSystemSettingsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'auto_increment' => true,
            ],
            'setting_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'setting_value' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('setting_key');
        $this->forge->createTable('system_settings', true);

        $now = date('Y-m-d H:i:s');
        $defaults = [
            // Core Identity
            ['setting_key' => 'app_name',            'setting_value' => 'Palompon Transit', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'app_subtitle',        'setting_value' => 'Terminal Monitor', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'acronym',             'setting_value' => 'PTTM', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'system_title',        'setting_value' => 'Palompon Transit Terminal Management System', 'created_at' => $now, 'updated_at' => $now],
            
            // Media Assets (null = use system default fallback)
            ['setting_key' => 'app_logo',            'setting_value' => null, 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'app_background_image','setting_value' => null, 'created_at' => $now, 'updated_at' => $now],
            
            // Role Theme Templates
            ['setting_key' => 'theme_guest_primary', 'setting_value' => '#1E40AF', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'theme_guest_nav_bg',  'setting_value' => '#ffffff', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'theme_guest_nav_text','setting_value' => '#1c2430', 'created_at' => $now, 'updated_at' => $now],
            
            ['setting_key' => 'theme_staff_primary', 'setting_value' => '#15803d', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'theme_staff_nav_bg',  'setting_value' => '#15803d', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'theme_staff_nav_text','setting_value' => '#ffffff', 'created_at' => $now, 'updated_at' => $now],
            
            ['setting_key' => 'theme_admin_primary', 'setting_value' => '#B71C1C', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'theme_admin_nav_bg',  'setting_value' => '#B71C1C', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'theme_admin_nav_text','setting_value' => '#ffffff', 'created_at' => $now, 'updated_at' => $now],
            
            // Footer & Information
            ['setting_key' => 'footer_about_title',  'setting_value' => 'PTTM System', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'footer_about_text',   'setting_value' => 'Palompon Transit Terminal Management System provides real-time tracking of vehicle queues and departure schedules to ensure efficient travel for every passenger.', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'footer_credit',       'setting_value' => 'Municipality of Palompon, Leyte', 'created_at' => $now, 'updated_at' => $now],
            
            // Contact
            ['setting_key' => 'contact_email',       'setting_value' => '', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'contact_phone',       'setting_value' => '(053) 555-8376 / 338-2022', 'created_at' => $now, 'updated_at' => $now],
            ['setting_key' => 'contact_address',     'setting_value' => 'Palompon Transit Terminal, Rizal St., Palompon, Leyte 6538', 'created_at' => $now, 'updated_at' => $now],
        ];

        foreach ($defaults as $row) {
            $this->db->table('system_settings')->ignore(true)->insert($row);
        }
    }

    public function down()
    {
        $this->forge->dropTable('system_settings', true);
    }
}
