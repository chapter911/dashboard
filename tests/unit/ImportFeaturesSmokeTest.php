<?php

use App\Controllers\C_Laporan;
use App\Controllers\C_Master;
use App\Controllers\C_P2TL;
use App\Models\LaporanModel;
use App\Models\P2TLModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

/**
 * @internal
 */
final class ImportFeaturesSmokeTest extends CIUnitTestCase
{
    public function testP2TLImportDataSmokeSuccess(): void
    {
        $source = $this->createTempP2TLDataWorkbook();
        $uploaded = $this->buildMovedUploadMock($source, 'xlsx', 'smoke_import_data.xlsx');

        $request = $this->createMock(IncomingRequest::class);
        $request->method('getFile')->with('file_import')->willReturn($uploaded);

        $model = $this->createMock(P2TLModel::class);
        $model->expects($this->once())
            ->method('upsertP2TLByAgenda')
            ->with($this->callback(static function (array $rows): bool {
                if (count($rows) !== 1) {
                    return false;
                }

                $row = $rows[0];
                return ($row['no_agenda'] ?? '') === 'AGD-001'
                    && ($row['idpel'] ?? '') === '12345678901'
                    && ($row['gol'] ?? '') === 'R1'
                    && (float) ($row['kwh'] ?? 0) === 245.0
                    && (int) ($row['unit_id'] ?? 0) === 54110;
            }));

        session()->set(['username' => 'smoke.tester']);

        $controller = new C_P2TL();
        $controller->initController($request, service('response'), service('logger'));
        $this->setPrivateProperty($controller, 'p2tlModel', $model);

        $response = $controller->importData();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('Data P2TL berhasil diimport.', session()->getFlashdata('success'));
        $this->assertFileDoesNotExist(WRITEPATH . 'uploads/smoke_import_data.xlsx');
        @unlink($source);
    }

    public function testP2TLImportHitRateSmokeSuccess(): void
    {
        $source = $this->createTempCsvFile("idpel,daya,kwh_ts\n12345678901,1300,240\n");
        $uploaded = $this->buildMovedUploadMock($source, 'csv', 'smoke_import_hitrate.csv');

        $request = $this->createMock(IncomingRequest::class);
        $request->method('getFile')->with('file_import')->willReturn($uploaded);

        $model = $this->createMock(P2TLModel::class);
        $model->expects($this->once())
            ->method('insertHitrateBatch')
            ->with($this->callback(static function (array $rows): bool {
                if (count($rows) !== 1) {
                    return false;
                }

                $row = $rows[0];
                return ($row['idpel'] ?? '') === '12345678901'
                    && (float) ($row['daya'] ?? 0) === 1300.0
                    && (float) ($row['kwh_ts'] ?? 0) === 240.0
                    && ($row['created_by'] ?? '') === 'smoke.tester';
            }));

        $db = $this->createMock(BaseConnection::class);
        $db->method('getFieldNames')->with('trn_hitrate')->willReturn(['idpel', 'daya', 'kwh_ts', 'created_by']);

        session()->set(['username' => 'smoke.tester']);

        $controller = new C_P2TL();
        $controller->initController($request, service('response'), service('logger'));
        $this->setPrivateProperty($controller, 'p2tlModel', $model);
        $this->setPrivateProperty($controller, 'db', $db);

        $response = $controller->importHitRate();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('Data hitrate berhasil diimport.', session()->getFlashdata('success'));
        $this->assertFileDoesNotExist(WRITEPATH . 'uploads/smoke_import_hitrate.csv');
        @unlink($source);
    }

    public function testP2TLImportTargetOperasiSmokeSuccess(): void
    {
        $source = $this->createTempTargetOperasiWorkbook();
        $uploaded = $this->buildMovedUploadMock($source, 'xlsx', 'smoke_import_target_operasi.xlsx');

        $request = $this->createMock(IncomingRequest::class);
        $request->method('getFile')->with('file_import')->willReturn($uploaded);

        $model = $this->createMock(P2TLModel::class);
        $model->expects($this->once())
            ->method('insertTargetOperasiBatch')
            ->with($this->callback(static function (array $rows): bool {
                if (count($rows) !== 1) {
                    return false;
                }

                $row = $rows[0];
                return ($row['idpel'] ?? '') === '12345678901'
                    && ($row['nama'] ?? '') === 'Pelanggan Uji'
                    && (float) ($row['jam_nyala'] ?? 0) === 15.5
                    && ($row['jenis_to'] ?? '') === 'P2TL'
                    && (int) ($row['unit_id'] ?? 0) === 54110;
            }));

        session()->set([
            'unit_id' => 54110,
            'username' => 'smoke.tester',
        ]);

        $controller = new C_P2TL();
        $controller->initController($request, service('response'), service('logger'));
        $this->setPrivateProperty($controller, 'p2tlModel', $model);

        $response = $controller->importTargetOperasi();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('Data target operasi berhasil diimport.', session()->getFlashdata('success'));
        $this->assertFileDoesNotExist(WRITEPATH . 'uploads/smoke_import_target_operasi.xlsx');
        @unlink($source);
    }

    public function testLaporanImportHarianSmokeSuccess(): void
    {
        $csv = $this->createTempCsvFile("NO_AGENDA,IDPEL\nAGD-01,12345678901\n");

        $uploaded = $this->createMock(UploadedFile::class);
        $uploaded->method('isValid')->willReturn(true);
        $uploaded->method('hasMoved')->willReturn(false);
        $uploaded->method('getClientExtension')->willReturn('csv');
        $uploaded->method('getTempName')->willReturn($csv);

        $request = $this->createMock(IncomingRequest::class);
        $request->method('getFile')->with('file_import')->willReturn($uploaded);

        $model = new class () extends LaporanModel {
            public ?array $lastInsert = null;

            public function where($key = null, $value = null, ?bool $escape = null)
            {
                return $this;
            }

            public function first()
            {
                return null;
            }

            public function insert($data = null, bool $returnID = true)
            {
                $this->lastInsert = is_array($data) ? $data : null;
                return 1;
            }
        };

        $controller = new C_Laporan();
        $controller->initController($request, service('response'), service('logger'));
        $this->setPrivateProperty($controller, 'laporanModel', $model);

        $response = $controller->importHarian();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertIsArray($model->lastInsert);
        $this->assertSame('AGD-01', (string) ($model->lastInsert['no_agenda'] ?? ''));
        @unlink($csv);
    }

    public function testMasterImportPelangganSmokeSuccess(): void
    {
        $this->prepareMasterPelangganTable();

        $source = $this->createTempMasterPelangganWorkbook();
        $uploaded = $this->buildMovedUploadMock($source, 'xlsx', 'smoke_import_pelanggan.xlsx', 1.0);

        $request = $this->createMock(IncomingRequest::class);
        $request->method('getFile')->with('excel_file')->willReturn($uploaded);

        $controller = new C_Master();
        $controller->initController($request, service('response'), service('logger'));

        $response = $controller->importPelanggan();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $success = (string) session()->getFlashdata('success');
        $error = (string) session()->getFlashdata('error');
        $this->assertNotSame('', $success, 'Import pelanggan gagal: ' . $error);
        $this->assertStringContainsString('Import data induk langganan berhasil', $success);

        $count = (int) db_connect()->table('mst_data_induk_langganan')->countAllResults();
        $this->assertSame(1, $count);

        @unlink($source);
    }

    private function buildMovedUploadMock(string $sourcePath, string $extension, string $targetName, float $sizeMb = 0.1): UploadedFile
    {
        $uploaded = $this->createMock(UploadedFile::class);
        $uploaded->method('isValid')->willReturn(true);
        $uploaded->method('hasMoved')->willReturn(false);
        $uploaded->method('getClientExtension')->willReturn($extension);
        $uploaded->method('getRandomName')->willReturn($targetName);
        $uploaded->method('getSizeByUnit')->with('mb')->willReturn($sizeMb);
        $uploaded->method('move')->willReturnCallback(static function (string $targetPath, ?string $name = null) use ($sourcePath): bool {
            if (! is_dir($targetPath)) {
                mkdir($targetPath, 0777, true);
            }

            $filename = $name ?? basename($sourcePath);
            return copy($sourcePath, rtrim($targetPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename);
        });

        return $uploaded;
    }

    private function createTempCsvFile(string $content): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'smk_csv_');
        if ($tmp === false) {
            $this->fail('Gagal membuat temporary CSV file.');
        }

        file_put_contents($tmp, $content);
        return $tmp;
    }

    private function createTempP2TLDataWorkbook(): string
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        for ($i = 1; $i <= 8; $i++) {
            $sheet->setCellValue('A' . $i, '');
        }

        $row = 9;
        $sheet->setCellValue('B' . $row, 'AGD-001');
        $sheet->setCellValue('D' . $row, '12345678901');
        $sheet->setCellValue('E' . $row, 'Pelanggan Uji');
        $sheet->setCellValue('H' . $row, 'R1');
        $sheet->setCellValue('I' . $row, 'Jl. Uji');
        $sheet->setCellValue('K' . $row, 1300);
        $sheet->setCellValue('L' . $row, 245);
        $sheet->setCellValue('AE' . $row, '54110REG001');
        $sheet->setCellValue('AH' . $row, 'SPH-001');

        $tmp = tempnam(sys_get_temp_dir(), 'smk_p2tl_data_');
        if ($tmp === false) {
            $this->fail('Gagal membuat temporary workbook P2TL data.');
        }

        $path = $tmp . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($path);
        @unlink($tmp);

        return $path;
    }

    private function createTempTargetOperasiWorkbook(): string
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray(['NO', 'IDPEL', 'NAMA', 'TARIF', 'DAYA', 'GARDU', 'TIANG', 'JAM_NYALA', 'JENIS_TO', 'LAT', 'LNG', 'SUBDLPD'], null, 'A1');
        $sheet->fromArray([1, '12345678901', 'Pelanggan Uji', 'R1', 1300, 'GRD01', 'TG01', 15.5, 'P2TL', '-6.2', '106.8', 'SUB-A'], null, 'A2');

        $tmp = tempnam(sys_get_temp_dir(), 'smk_to_');
        if ($tmp === false) {
            $this->fail('Gagal membuat temporary workbook target operasi.');
        }

        $path = $tmp . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($path);
        @unlink($tmp);

        return $path;
    }

    private function prepareMasterPelangganTable(): void
    {
        $db = db_connect();
        $forge = Database::forge();

        if ($db->tableExists('mst_data_induk_langganan')) {
            $forge->dropTable('mst_data_induk_langganan', true);
        }

        $forge->addField([
            'idpel' => [
                'type' => 'INTEGER',
                'null' => false,
            ],
            'v_bulan_rekap' => [
                'type' => 'INTEGER',
                'null' => false,
            ],
        ]);
        $forge->createTable('mst_data_induk_langganan', true);
    }

    private function createTempMasterPelangganWorkbook(): string
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(['IMPORT DATA PELANGGAN'], null, 'A1');
        $sheet->fromArray(['IDPEL', 'V_BULAN_REKAP'], null, 'A2');
        $sheet->fromArray([12345678901, 202601], null, 'A3');

        $tmp = tempnam(sys_get_temp_dir(), 'smk_master_');
        if ($tmp === false) {
            $this->fail('Gagal membuat temporary workbook master pelanggan.');
        }

        $path = $tmp . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($path);
        @unlink($tmp);

        return $path;
    }
}
