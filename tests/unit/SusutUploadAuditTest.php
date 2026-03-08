<?php

use App\Controllers\C_Susut;
use App\Models\SusutModel;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

/**
 * @internal
 */
final class SusutUploadAuditTest extends CIUnitTestCase
{
    public function testNormalizeTargetValueHandlesNumericAndPercentInputs(): void
    {
        $controller = new C_Susut();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('normalizeTargetValue');
        $method->setAccessible(true);

        $this->assertSame(5.88, $method->invoke($controller, '5,88%'));
        $this->assertSame(5.88, $method->invoke($controller, 0.0588));
        $this->assertSame(5.88, $method->invoke($controller, '5.88'));
        $this->assertSame(0.0, $method->invoke($controller, ''));
        $this->assertSame(0.0, $method->invoke($controller, 'abc'));
    }

    public function testNormalizeUnitNameCondensesWhitespace(): void
    {
        $controller = new C_Susut();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('normalizeUnitName');
        $method->setAccessible(true);

        $this->assertSame('BANDENGAN', $method->invoke($controller, ' bandengan '));
        $this->assertSame('KEBUN JERUK', $method->invoke($controller, "  Kebun\t\tJeruk  "));
    }

    public function testReplaceTargetByYearReplacesOnlySelectedYearData(): void
    {
        $db = db_connect();
        $forge = Database::forge();

        if ($db->tableExists('trn_target_susut')) {
            $forge->dropTable('trn_target_susut', true);
        }

        $forge->addField([
            'id' => [
                'type' => 'INTEGER',
                'constraint' => 11,
                'auto_increment' => true,
            ],
            'unit_id' => [
                'type' => 'INTEGER',
                'constraint' => 11,
                'null' => false,
            ],
            'bulan' => [
                'type' => 'INTEGER',
                'constraint' => 11,
                'null' => false,
            ],
            'tahun' => [
                'type' => 'INTEGER',
                'constraint' => 11,
                'null' => false,
            ],
            'nilai' => [
                'type' => 'REAL',
                'null' => true,
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_by' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
        ]);
        $forge->addKey('id', true);
        $forge->createTable('trn_target_susut', true);

        $db->table('trn_target_susut')->insertBatch([
            [
                'unit_id' => 54110,
                'bulan' => 1,
                'tahun' => 2025,
                'nilai' => 4.11,
                'created_by' => 'seed',
            ],
            [
                'unit_id' => 54110,
                'bulan' => 1,
                'tahun' => 2024,
                'nilai' => 3.22,
                'created_by' => 'seed',
            ],
        ]);

        $model = new SusutModel();
        $model->replaceTargetByYear(2025, [
            [
                'unit_id' => 54110,
                'bulan' => 1,
                'tahun' => 2025,
                'nilai' => 5.5,
                'created_by' => 'tester',
            ],
            [
                'unit_id' => 54130,
                'bulan' => 2,
                'tahun' => 2025,
                'nilai' => 6.6,
                'created_by' => 'tester',
            ],
        ]);

        $rows2025 = $db->table('trn_target_susut')->where('tahun', 2025)->get()->getResultArray();
        $rows2024 = $db->table('trn_target_susut')->where('tahun', 2024)->get()->getResultArray();

        $this->assertCount(2, $rows2025);
        $this->assertCount(1, $rows2024);

        $nilai2025 = array_map(static fn(array $row): float => (float) $row['nilai'], $rows2025);
        sort($nilai2025);

        $this->assertSame([5.5, 6.6], $nilai2025);
        $this->assertSame(3.22, (float) ($rows2024[0]['nilai'] ?? 0));
    }
}
