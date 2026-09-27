<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run()
    {
        if (ENVIRONMENT === 'production') {
            throw new \RuntimeException('Demo admin seeding is disabled in production.');
        }

        // Keep this development-only seeder idempotent. Never replace an
        // existing account or reset its password when the seeder is rerun.
        if ($this->db->table('users')->where('username', 'admin')->countAllResults() > 0) {
            return;
        }

        $data = [
            'username' => 'admin',
            'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'admin',
            'full_name' => 'System Administrator',
            'email' => 'admin-local@ttm.local',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->table('users')->insert($data);
    }
}
