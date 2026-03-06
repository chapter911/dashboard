<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SeedMstUnitFromAttachment extends Migration
{
    /**
     * @var array<int, array<string, int|string|null>>
     */
    private array $units = [
        ['unit_id' => 54110, 'unit_name' => 'MENTENG', 'unit_singkatan' => 'MTG', 'is_active' => 1, 'created_by' => 'system', 'created_date' => '2025-10-06', 'updated_by' => null, 'updated_date' => null, 'urutan' => 1],
        ['unit_id' => 54130, 'unit_name' => 'CEMPAKA PUTIH', 'unit_singkatan' => 'CPP', 'is_active' => 1, 'created_by' => 'system', 'created_date' => '2025-10-06', 'updated_by' => null, 'updated_date' => null, 'urutan' => 2],
        ['unit_id' => 54310, 'unit_name' => 'BULUNGAN', 'unit_singkatan' => 'BLG', 'is_active' => 1, 'created_by' => 'system', 'created_date' => '2025-10-06', 'updated_by' => null, 'updated_date' => null, 'urutan' => 4],
        ['unit_id' => 54210, 'unit_name' => 'BANDENGAN', 'unit_singkatan' => 'BDG', 'is_active' => 1, 'created_by' => 'system', 'created_date' => '2025-10-06', 'updated_by' => null, 'updated_date' => null, 'urutan' => 3],
        ['unit_id' => 54330, 'unit_name' => 'KEBON JERUK', 'unit_singkatan' => 'KBJ', 'is_active' => 1, 'created_by' => 'system', 'created_date' => '2025-10-06', 'updated_by' => null, 'updated_date' => null, 'urutan' => 5],
        ['unit_id' => 54360, 'unit_name' => 'CIPUTAT', 'unit_singkatan' => 'CPT', 'is_active' => 1, 'created_by' => 'system', 'created_date' => '2025-10-06', 'updated_by' => null, 'updated_date' => null, 'urutan' => 6],
        ['unit_id' => 54380, 'unit_name' => 'BINTARO', 'unit_singkatan' => 'BTR', 'is_active' => 1, 'created_by' => 'system', 'created_date' => '2025-10-06', 'updated_by' => null, 'updated_date' => null, 'urutan' => 7],
        ['unit_id' => 54410, 'unit_name' => 'JATINEGARA', 'unit_singkatan' => 'JTN', 'is_active' => 1, 'created_by' => 'system', 'created_date' => '2025-10-06', 'updated_by' => null, 'updated_date' => null, 'urutan' => 8],
        ['unit_id' => 54420, 'unit_name' => 'PONDOK KOPI', 'unit_singkatan' => 'PDK', 'is_active' => 1, 'created_by' => 'system', 'created_date' => '2025-10-06', 'updated_by' => null, 'updated_date' => null, 'urutan' => 9],
        ['unit_id' => 54510, 'unit_name' => 'TANJUNG PRIOK', 'unit_singkatan' => 'TJP', 'is_active' => 1, 'created_by' => 'system', 'created_date' => '2025-10-06', 'updated_by' => null, 'updated_date' => null, 'urutan' => 10],
        ['unit_id' => 54530, 'unit_name' => 'MARUNDA', 'unit_singkatan' => 'MRD', 'is_active' => 1, 'created_by' => 'system', 'created_date' => '2025-10-06', 'updated_by' => null, 'updated_date' => null, 'urutan' => 11],
        ['unit_id' => 54630, 'unit_name' => 'CENGKARENG', 'unit_singkatan' => 'CKR', 'is_active' => 1, 'created_by' => 'system', 'created_date' => '2025-10-06', 'updated_by' => null, 'updated_date' => null, 'urutan' => 12],
        ['unit_id' => 54710, 'unit_name' => 'KRAMAT JATI', 'unit_singkatan' => 'KJT', 'is_active' => 1, 'created_by' => 'system', 'created_date' => '2025-10-06', 'updated_by' => null, 'updated_date' => null, 'urutan' => 13],
        ['unit_id' => 54720, 'unit_name' => 'CIRACAS', 'unit_singkatan' => 'CRC', 'is_active' => 1, 'created_by' => 'system', 'created_date' => '2025-10-06', 'updated_by' => null, 'updated_date' => null, 'urutan' => 14],
        ['unit_id' => 54730, 'unit_name' => 'PONDOK GEDE', 'unit_singkatan' => 'PDG', 'is_active' => 1, 'created_by' => 'system', 'created_date' => '2025-10-06', 'updated_by' => null, 'updated_date' => null, 'urutan' => 15],
        ['unit_id' => 54740, 'unit_name' => 'LENTENG AGUNG', 'unit_singkatan' => 'LTA', 'is_active' => 1, 'created_by' => 'system', 'created_date' => '2025-10-06', 'updated_by' => null, 'updated_date' => null, 'urutan' => 16],
        ['unit_id' => 54000, 'unit_name' => 'UID JAYA', 'unit_singkatan' => 'UID', 'is_active' => 1, 'created_by' => 'system', 'created_date' => '2025-11-18', 'updated_by' => null, 'updated_date' => null, 'urutan' => 17],
    ];

    public function up()
    {
        if (! $this->db->tableExists('mst_unit')) {
            return;
        }

        foreach ($this->units as $unit) {
            $exists = $this->db->table('mst_unit')
                ->where('unit_id', (int) $unit['unit_id'])
                ->countAllResults() > 0;

            if ($exists) {
                $this->db->table('mst_unit')
                    ->where('unit_id', (int) $unit['unit_id'])
                    ->update($unit);
            } else {
                $this->db->table('mst_unit')->insert($unit);
            }
        }
    }

    public function down()
    {
        // No-op to avoid deleting master unit data from production environments.
    }
}
