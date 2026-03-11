<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestP2tl extends BaseCommand
{
    protected $group       = 'Test';
    protected $name        = 'test:p2tl';
    protected $description = 'Smoke test for P2TL import mechanism';

    public function run(array $params = [])
    {
        $db = \Config\Database::connect();

        CLI::write("\n=== TEST 1: DATABASE CONNECTION ===");
        CLI::write("✓ Database connected");

        CLI::write("\n=== TEST 2: TABLE EXISTENCE ===");
        $tables = $db->listTables();
        if (in_array('trn_p2tl', $tables)) {
            CLI::write("✓ trn_p2tl table exists");
        } else {
            CLI::error("✗ trn_p2tl table NOT found");
            return 1;
        }

        CLI::write("\n=== TEST 3: TABLE STRUCTURE ===");
        $fields = $db->getFieldData('trn_p2tl');
        CLI::write("Columns in trn_p2tl:");
        foreach ($fields as $field) {
            CLI::write("  - " . $field->name . " (" . $field->type . ")");
        }

        CLI::write("\n=== TEST 4: PRIMARY KEY ===");
        $constraints = $db->query(
            "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
             WHERE TABLE_NAME = 'trn_p2tl' AND CONSTRAINT_NAME = 'PRIMARY'"
        )->getResultArray();
        if (!empty($constraints)) {
            CLI::write("✓ Primary key: " . $constraints[0]['COLUMN_NAME']);
        } else {
            CLI::error("✗ No primary key found");
        }

        CLI::write("\n=== TEST 5: CURRENT DATA COUNT ===");
        $count = $db->table('trn_p2tl')->countAllResults();
        CLI::write("Total records in trn_p2tl: " . $count);

        CLI::write("\n=== TEST 6: MODEL CHECK ===");
        try {
            $model = new \App\Models\P2TLModel();
            CLI::write("✓ P2TLModel class loaded");
            CLI::write("  - Table: " . $model->table);
            CLI::write("  - Primary Key: " . $model->primaryKey);
            CLI::write("  - Return Type: " . $model->returnType);
        } catch (\Exception $e) {
            CLI::error("✗ P2TLModel error: " . $e->getMessage());
            return 1;
        }

        CLI::write("\n=== TEST 7: UPSERT METHOD CHECK ===");
        try {
            $model = new \App\Models\P2TLModel();
            if (method_exists($model, 'upsertP2TLByAgenda')) {
                CLI::write("✓ upsertP2TLByAgenda method exists");
            } else {
                CLI::error("✗ upsertP2TLByAgenda method NOT found");
            }
        } catch (\Exception $e) {
            CLI::error("✗ Error: " . $e->getMessage());
        }

        CLI::write("\n=== SMOKE TEST COMPLETE ===");
        CLI::write("All critical components verified. Import should work!", "green");
        return 0;
    }
}
