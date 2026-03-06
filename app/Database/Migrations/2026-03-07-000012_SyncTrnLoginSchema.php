<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SyncTrnLoginSchema extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('trn_login')) {
            return;
        }

        $this->addMissingAuditColumns();
        $this->backfillEventType();
        $this->ensureAuditIndex();
    }

    public function down()
    {
        if (! $this->db->tableExists('trn_login')) {
            return;
        }

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

    private function addMissingAuditColumns(): void
    {
        if (! $this->db->fieldExists('event_type', 'trn_login')) {
            $this->forge->addColumn('trn_login', [
                'event_type' => [
                    'type' => 'VARCHAR',
                    'constraint' => 30,
                    'null' => true,
                    'after' => 'username',
                ],
            ]);
        }

        if (! $this->db->fieldExists('ip_address', 'trn_login')) {
            $this->forge->addColumn('trn_login', [
                'ip_address' => [
                    'type' => 'VARCHAR',
                    'constraint' => 45,
                    'null' => true,
                    'after' => 'is_logged_in',
                ],
            ]);
        }

        if (! $this->db->fieldExists('ip_network', 'trn_login')) {
            $this->forge->addColumn('trn_login', [
                'ip_network' => [
                    'type' => 'VARCHAR',
                    'constraint' => 80,
                    'null' => true,
                    'after' => 'ip_address',
                ],
            ]);
        }

        if (! $this->db->fieldExists('user_agent', 'trn_login')) {
            $this->forge->addColumn('trn_login', [
                'user_agent' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'after' => 'ip_network',
                ],
            ]);
        }

        if (! $this->db->fieldExists('notes', 'trn_login')) {
            $this->forge->addColumn('trn_login', [
                'notes' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'after' => 'user_agent',
                ],
            ]);
        }
    }

    private function backfillEventType(): void
    {
        if (! $this->db->fieldExists('event_type', 'trn_login')) {
            return;
        }

        $this->db->query(
            "UPDATE trn_login
             SET event_type = IF((is_logged_in + 0) = 1, 'LOGIN_SUCCESS', 'LOGOUT')
             WHERE COALESCE(event_type, '') = ''"
        );
    }

    private function ensureAuditIndex(): void
    {
        $indexes = $this->db->getIndexData('trn_login');
        if (isset($indexes['idx_trn_login_username_created'])) {
            return;
        }

        $this->db->query('CREATE INDEX idx_trn_login_username_created ON trn_login(username, created_date)');
    }
}
