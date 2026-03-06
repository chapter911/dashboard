<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class HardenAuthSchema extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('trn_login')) {
            $newColumns = [];

            if (! $this->db->fieldExists('event_type', 'trn_login')) {
                $newColumns['event_type'] = [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                    'null' => true,
                    'after' => 'username',
                ];
            }

            if (! $this->db->fieldExists('ip_address', 'trn_login')) {
                $newColumns['ip_address'] = [
                    'type' => 'VARCHAR',
                    'constraint' => 45,
                    'null' => true,
                    'after' => 'is_logged_in',
                ];
            }

            if (! $this->db->fieldExists('ip_network', 'trn_login')) {
                $newColumns['ip_network'] = [
                    'type' => 'VARCHAR',
                    'constraint' => 80,
                    'null' => true,
                    'after' => 'ip_address',
                ];
            }

            if (! $this->db->fieldExists('user_agent', 'trn_login')) {
                $newColumns['user_agent'] = [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'after' => 'ip_network',
                ];
            }

            if (! $this->db->fieldExists('notes', 'trn_login')) {
                $newColumns['notes'] = [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'after' => 'user_agent',
                ];
            }

            if ($newColumns !== []) {
                $this->forge->addColumn('trn_login', $newColumns);
            }
        }

        if ($this->db->tableExists('mst_user') && $this->db->fieldExists('password', 'mst_user')) {
            $rows = $this->db->table('mst_user')
                ->select('username, password')
                ->where('password IS NOT NULL', null, false)
                ->where('password <>', '')
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                $storedPassword = (string) ($row['password'] ?? '');

                if ($storedPassword === '') {
                    continue;
                }

                $hashInfo = password_get_info($storedPassword);

                if (($hashInfo['algo'] ?? 0) !== 0) {
                    continue;
                }

                $this->db->table('mst_user')
                    ->where('username', (string) $row['username'])
                    ->update([
                        'password' => password_hash($storedPassword, PASSWORD_DEFAULT),
                    ]);
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('trn_login')) {
            $dropColumns = [];

            foreach (['event_type', 'ip_address', 'ip_network', 'user_agent', 'notes'] as $column) {
                if ($this->db->fieldExists($column, 'trn_login')) {
                    $dropColumns[] = $column;
                }
            }

            if ($dropColumns !== []) {
                $this->forge->dropColumn('trn_login', $dropColumns);
            }
        }

        // Password hashes are intentionally not reverted to plaintext for security reasons.
    }
}
