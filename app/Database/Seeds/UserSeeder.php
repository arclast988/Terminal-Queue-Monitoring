<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = $this->db->table('users');
        $hasSuperAdmin = $users->where('role', 'super_admin')->countAllResults() > 0;

        $data = [
            [
                'username' => 'admin@ttm.local',
                'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => $hasSuperAdmin ? 'admin' : 'super_admin',
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

        // Seed only missing demo accounts. Never delete or reset an existing
        // account, password, role, or route assignment when this seeder reruns.
        foreach ($data as $user) {
            $exists = $this->db->table('users')
                ->groupStart()
                    ->where('username', $user['username'])
                    ->orWhere('email', $user['email'])
                ->groupEnd()
                ->countAllResults() > 0;

            if (!$exists) {
                $this->db->table('users')->insert($user);
            }
        }
    }
}
