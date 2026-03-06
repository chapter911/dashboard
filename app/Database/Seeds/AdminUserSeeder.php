<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $username = 'admin';

        $data = [
            'username' => $username,
            'nama' => 'Administrator',
            'email' => 'admin@dashboard.local',
            'password' => password_hash('Kiliki@123', PASSWORD_DEFAULT),
            'group_id' => 1,
            'is_active' => 1,
            'web_access' => 1,
            'android_access' => 0,
            'created_by' => 'system-seeder',
        ];

        $existing = $this->db->table('mst_user')
            ->select('username')
            ->where('username', $username)
            ->get()
            ->getRowArray();

        if ($existing) {
            $this->db->table('mst_user')
                ->where('username', $username)
                ->update($data);

            return;
        }

        $this->db->table('mst_user')->insert($data);
    }
}
