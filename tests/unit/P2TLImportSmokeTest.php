<?php

use App\Controllers\C_P2TL;
use App\Models\P2TLModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class P2TLImportSmokeTest extends CIUnitTestCase
{
    public function testImportAnalisaSmokeSuccess(): void
    {
        $tmpFile = $this->createTempAnalisaWorkbook();

        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->method('isValid')->willReturn(true);
        $uploadedFile->method('hasMoved')->willReturn(false);
        $uploadedFile->method('getClientExtension')->willReturn('xlsx');
        $uploadedFile->method('getRandomName')->willReturn('smoke_import_analisa.xlsx');
        $uploadedFile->method('move')->willReturnCallback(static function (string $targetPath, ?string $name = null) use ($tmpFile): bool {
            if (! is_dir($targetPath)) {
                mkdir($targetPath, 0777, true);
            }

            $filename = $name ?? basename($tmpFile);
            return copy($tmpFile, rtrim($targetPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename);
        });

        $request = $this->createMock(IncomingRequest::class);
        $request->method('getFile')->with('file_import')->willReturn($uploadedFile);
        $request->method('getPost')->willReturnCallback(static function (?string $key = null) {
            $map = [
                'tahun' => 2026,
                'bulan' => 3,
                'unit_id' => 54110,
            ];

            if ($key === null) {
                return $map;
            }

            return $map[$key] ?? null;
        });

        $model = $this->createMock(P2TLModel::class);
        $model->expects($this->once())
            ->method('replaceAnalisaByPeriodUnit')
            ->with(
                '2026-03-01',
                54110,
                $this->callback(static function (array $rows): bool {
                    if (count($rows) !== 1) {
                        return false;
                    }

                    $row = $rows[0];

                    return ($row['idpel'] ?? '') === '12345678901'
                        && ($row['tarif'] ?? '') === 'R1'
                        && (float) ($row['daya'] ?? 0) === 1300.0
                        && (float) ($row['pemakaian_kwh'] ?? 0) === 245.0
                        && ($row['periode'] ?? '') === '2026-03-01'
                        && (int) ($row['unit_id'] ?? 0) === 54110;
                }),
            );

        session()->set([
            'group_id' => 1,
            'unit_id' => 54110,
            'username' => 'smoke.tester',
        ]);

        $controller = new C_P2TL();
        $controller->initController($request, service('response'), service('logger'));

        $ref = new ReflectionClass($controller);
        $prop = $ref->getProperty('p2tlModel');
        $prop->setAccessible(true);
        $prop->setValue($controller, $model);

        $response = $controller->importAnalisa();

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('Import analisa berhasil.', session()->getFlashdata('success'));
        $this->assertFileDoesNotExist(WRITEPATH . 'uploads/smoke_import_analisa.xlsx');

        @unlink($tmpFile);
    }

    private function createTempAnalisaWorkbook(): string
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(['IDPEL', 'TARIF', 'DAYA', 'PEMAKAIAN_KWH'], null, 'A1');
        $sheet->fromArray(['12345678901', 'R1', 1300, 245], null, 'A2');

        $tmpFile = tempnam(sys_get_temp_dir(), 'p2tl_smoke_');
        if ($tmpFile === false) {
            $this->fail('Gagal membuat file temporary untuk smoke test import.');
        }

        $xlsxPath = $tmpFile . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($xlsxPath);
        @unlink($tmpFile);

        return $xlsxPath;
    }
}
