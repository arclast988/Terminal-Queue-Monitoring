<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $data = [
            'username' => 'admin',
            'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'admin',
            'full_name' => 'System Administrator',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        // Ensure no duplicate simple admin exists
        $this->db->table('users')->where('username', 'admin')->delete();
        $this->db->table('users')->insert($data);
    }
}
