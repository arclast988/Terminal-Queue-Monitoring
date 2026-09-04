<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'username' => 'admin',
                'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'super_admin',
                'full_name' => 'System Administrator',
                'email' => 'arclast988@gmail.com',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'username' => 'staff',
                'password_hash' => password_hash('staff123', PASSWORD_DEFAULT),
                'role' => 'staff',
                'full_name' => 'Staff User',
                'email' => 'staff@example.com',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'username' => 'staff2',
                'password_hash' => password_hash('staff123', PASSWORD_DEFAULT),
                'role' => 'staff',
                'full_name' => 'Dispatcher User',
                'email' => 'staff2@example.com',
                'created_at' => date('Y-m-d H:i:s'),
            ]
        ];

        // Clean up existing seeded users before re-inserting
        $this->db->table('users')->whereIn('username', ['admin', 'staff', 'staff2'])->delete();
        $this->db->table('users')->insertBatch($data);
    }
}
