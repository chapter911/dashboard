<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

class ImportAttachedMenuAccessData extends Migration
{
    private const SOURCE_SQL_FILE = ROOTPATH . 'menu_akses.sql';

    /**
     * Only import menu data to match existing app structure safely.
     * User and group data in the attached file are intentionally skipped.
     *
     * @var array<int, string>
     */
    private array $allowedTables = ['menu_akses', 'menu_lv1', 'menu_lv2', 'menu_lv3'];

    public function up()
    {
        if (! is_file(self::SOURCE_SQL_FILE)) {
            throw new RuntimeException('Source SQL file not found: ' . self::SOURCE_SQL_FILE);
        }

        $content = file_get_contents(self::SOURCE_SQL_FILE);

        if (! is_string($content) || trim($content) === '') {
            throw new RuntimeException('Source SQL file is empty or unreadable: ' . self::SOURCE_SQL_FILE);
        }

        $statements = preg_split('/;\s*(?:\r?\n|$)/', $content);

        if (! is_array($statements)) {
            return;
        }

        foreach ($statements as $statement) {
            $query = trim($statement);

            if ($query === '') {
                continue;
            }

            if (! preg_match('/^INSERT\s+INTO\s+`([^`]+)`/i', $query, $matches)) {
                continue;
            }

            $table = strtolower((string) ($matches[1] ?? ''));
            if (! in_array($table, $this->allowedTables, true)) {
                continue;
            }

            // Skip clearly invalid row found in source dump.
            if (stripos($query, "'group_id'") !== false) {
                continue;
            }

            $safeQuery = preg_replace('/^INSERT\s+INTO/i', 'INSERT IGNORE INTO', $query);

            if (! is_string($safeQuery) || trim($safeQuery) === '') {
                continue;
            }

            $this->db->query($safeQuery);
        }
    }

    public function down()
    {
        // Intentionally no-op: we do not delete existing menu data on rollback.
    }
}
