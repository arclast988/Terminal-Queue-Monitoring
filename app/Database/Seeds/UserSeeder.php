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
                'role' => 'admin',
                'full_name' => 'System Administrator',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'username' => 'staff',
                'password_hash' => password_hash('staff123', PASSWORD_DEFAULT),
                'role' => 'staff',
                'full_name' => 'Staff User',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'username' => 'operator',
                'password_hash' => password_hash('operator123', PASSWORD_DEFAULT),
                'role' => 'operator',
                'full_name' => 'Operator User',
                'created_at' => date('Y-m-d H:i:s'),
            ]
        ];

        // Using query builder
        $this->db->table('users')->insertBatch($data);
    }
}
