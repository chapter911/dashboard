<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ImportAttachedMenuAccessData extends Migration
{
    /**
     * Try these files in order. First existing non-empty file will be used.
     *
     * - database/seeds/menu_data.sql: recommended source exported from development DB
     * - menu_akses.sql: legacy source file path used previously
     *
     * @var array<int, string>
     */
    private const SOURCE_SQL_FILES = [
        ROOTPATH . 'database/seeds/menu_data.sql',
        ROOTPATH . 'menu_akses.sql',
    ];

    /**
     * Only import menu data to match existing app structure safely.
     * User and group data in the attached file are intentionally skipped.
     *
     * @var array<int, string>
     */
    private array $allowedTables = ['menu_akses', 'menu_lv1', 'menu_lv2', 'menu_lv3'];

    public function up()
    {
        $content = $this->getSourceSqlContent();
        if ($content === null) {
            log_message('warning', 'MENU_IMPORT_SOURCE_NOT_FOUND_OR_EMPTY. Migration skipped safely.');

            return;
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

    private function getSourceSqlContent(): ?string
    {
        foreach (self::SOURCE_SQL_FILES as $path) {
            if (! is_file($path)) {
                continue;
            }

            $content = file_get_contents($path);
            if (! is_string($content) || trim($content) === '') {
                continue;
            }

            log_message('info', 'MENU_IMPORT_SOURCE_USED: {path}', ['path' => $path]);

            return $content;
        }

        return null;
    }
}
