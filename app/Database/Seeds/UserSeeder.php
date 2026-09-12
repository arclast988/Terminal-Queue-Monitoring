<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'username' => 'admin@ttm.local',
                'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'super_admin',
                'full_name' => 'System Administrator',
                'email' => 'admin@ttm.local',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'username' => 'staff@ttm.local',
                'password_hash' => password_hash('staff123', PASSWORD_DEFAULT),
                'role' => 'staff',
                'full_name' => 'Staff User',
                'email' => 'staff@ttm.local',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'username' => 'staff2@ttm.local',
                'password_hash' => password_hash('staff123', PASSWORD_DEFAULT),
                'role' => 'staff',
                'full_name' => 'Dispatcher User',
                'email' => 'staff2@ttm.local',
                'created_at' => date('Y-m-d H:i:s'),
            ]
        ];

        // Clean up existing seeded users before re-inserting
        $this->db->table('users')->whereIn('username', ['admin', 'staff', 'staff2', 'admin@ttm.local', 'staff@ttm.local', 'staff2@ttm.local'])->delete();
        $this->db->table('users')->insertBatch($data);
    }
}
