<?php

namespace App\Controllers;

use App\Models\SusutModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Throwable;

class C_Susut extends BaseController
{
    private SusutModel $susutModel;

    public function __construct()
    {
        $this->susutModel = new SusutModel();
    }

    public function index(): string
    {
        return view('susut/index', [
            'title' => 'Data Susut',
            'pageHeading' => 'Data Susut',
            'months' => $this->susutModel->getMonths(),
            'units' => $this->susutModel->getUnits(true, 'name'),
            'currentYear' => (int) date('Y'),
        ]);
    }

    public function getDataSusut(): string
    {
        $year = (int) ($this->request->getPost('tahun') ?? date('Y'));
        if ($year < 2000 || $year > ((int) date('Y') + 5)) {
            $year = (int) date('Y');
        }

        $tampilan = (string) ($this->request->getPost('tampilan') ?? 'tabel');
        $jenisSusut = strtolower((string) ($this->request->getPost('jenis_susut') ?? 'netto'));
        if (! in_array($jenisSusut, ['netto', 'bruto', 'semua'], true)) {
            $jenisSusut = 'netto';
        }

        $payload = [
            'bulan' => $this->susutModel->getMonths(),
            'unit' => $this->susutModel->getUnits(false, 'name'),
            'unitGrafik' => $this->susutModel->getUnits(true, 'name'),
            'data' => $this->susutModel->getSusutRowsByYear($year),
            'data_uid' => $this->susutModel->getSusutUidRowsByYear($year),
            'target_susut' => $this->susutModel->getTargetByYear($year),
            'jenis_susut' => $jenisSusut,
            'tahun' => $year,
        ];

        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        if ($tampilan === 'grafik') {
            return view('susut/index_grafik', $payload);
        }

        return view('susut/index_data', $payload);
    }

    public function target_susut(): string
    {
        return view('susut/target_susut', [
            'title' => 'Target Susut',
            'pageHeading' => 'Target Susut',
            'currentYear' => (int) date('Y'),
        ]);
    }

    public function get_target_susut_data(): string
    {
        $year = (int) ($this->request->getPost('tahun') ?? date('Y'));

        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        return view('susut/target_susut_data', [
            'tahun' => $year,
            'bulan' => $this->susutModel->getMonths(),
            'unit' => $this->susutModel->getUnits(true, 'urutan'),
            'data_target' => $this->susutModel->getTargetByYear($year),
        ]);
    }

    public function download_format_target_susut(): ResponseInterface
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['NO', 'UNIT', 'JAN', 'FEB', 'MAR', 'APR', 'MEI', 'JUN', 'JUL', 'AGS', 'SEP', 'OKT', 'NOV', 'DES'];
        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $sheet->getStyle($column . '1')->getFont()->setBold(true);
            $column++;
        }

        $units = $this->susutModel->getUnits(true, 'urutan');
        $row = 2;
        foreach ($units as $index => $unit) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, (string) ($unit['unit_name'] ?? ''));
            $row++;
        }

        foreach (range('A', 'N') as $colId) {
            $sheet->getColumnDimension($colId)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'Format_Target_Susut.xlsx';

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('Cache-Control', 'max-age=0')
            ->setBody($this->writeSpreadsheetToString($writer));
    }

    public function upload_target_susut(): RedirectResponse
    {
        $year = (int) ($this->request->getPost('tahun') ?? 0);
        $file = $this->request->getFile('excel_file');

        if ($year < 2000 || $year > ((int) date('Y') + 5)) {
            return redirect()->to(site_url('C_Susut/target_susut'))->with('error', 'Tahun target tidak valid.');
        }

        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return redirect()->to(site_url('C_Susut/target_susut'))->with('error', 'File upload tidak valid.');
        }

        $extension = strtolower((string) $file->getClientExtension());
        if (! in_array($extension, ['xlsx', 'xls'], true)) {
            return redirect()->to(site_url('C_Susut/target_susut'))->with('error', 'Hanya file Excel (.xlsx/.xls) yang didukung.');
        }

        // Keep upload reasonably bounded to avoid memory pressure from oversized files.
        if ($file->getSizeByUnit('mb') > 10) {
            return redirect()->to(site_url('C_Susut/target_susut'))->with('error', 'Ukuran file maksimal 10 MB.');
        }

        $tempName = $file->getRandomName();
        $targetPath = WRITEPATH . 'uploads/' . $tempName;

        try {
            $file->move(WRITEPATH . 'uploads', $tempName);

            $spreadsheet = IOFactory::load($targetPath);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = (int) $sheet->getHighestRow();

            $units = $this->susutModel->getUnits(true, 'urutan');
            $unitMap = [];
            foreach ($units as $unit) {
                $name = $this->normalizeUnitName((string) ($unit['unit_name'] ?? ''));
                if ($name === '') {
                    continue;
                }

                $unitMap[$name] = (int) ($unit['unit_id'] ?? 0);
            }

            $rows = [];
            $createdBy = (string) (session('username') ?? 'system');
            $matchedUnits = [];

            for ($row = 2; $row <= $highestRow; $row++) {
                $unitName = $this->normalizeUnitName((string) $sheet->getCell('B' . $row)->getFormattedValue());
                if ($unitName === '' || ! isset($unitMap[$unitName])) {
                    continue;
                }

                $unitId = $unitMap[$unitName];
                if (isset($matchedUnits[$unitId])) {
                    return redirect()->to(site_url('C_Susut/target_susut'))->with('error', 'Template berisi unit duplikat. Pastikan setiap unit hanya muncul sekali.');
                }
                $matchedUnits[$unitId] = true;

                for ($month = 1; $month <= 12; $month++) {
                    $columnIndex = $month + 2;
                    $columnLetter = Coordinate::stringFromColumnIndex($columnIndex);
                    $raw = $sheet->getCell($columnLetter . $row)->getCalculatedValue();
                    $nilai = $this->normalizeTargetValue($raw);

                    $rows[] = [
                        'unit_id' => $unitId,
                        'bulan' => $month,
                        'tahun' => $year,
                        'nilai' => $nilai,
                        'created_by' => $createdBy,
                    ];
                }
            }

            if ($rows === []) {
                return redirect()->to(site_url('C_Susut/target_susut'))->with('error', 'Tidak ada data valid yang dapat diproses. Gunakan format template terbaru dan pastikan nama unit sesuai.');
            }

            $this->susutModel->replaceTargetByYear($year, $rows);
        } catch (Throwable $e) {
            log_message('error', 'UPLOAD_TARGET_SUSUT_FAILED: {message}', ['message' => $e->getMessage()]);

            return redirect()->to(site_url('C_Susut/target_susut'))->with('error', 'Upload target susut gagal diproses.');
        } finally {
            if (is_file($targetPath)) {
                @unlink($targetPath);
            }
        }

        return redirect()->to(site_url('C_Susut/target_susut'))->with('success', 'Data target susut tahun ' . $year . ' berhasil diperbarui.');
    }

    public function getComparisonData(): ResponseInterface
    {
        $year1 = (int) ($this->request->getPost('year1') ?? 0);
        $year2 = (int) ($this->request->getPost('year2') ?? 0);
        $unitId = (int) ($this->request->getPost('unit_id') ?? 0);
        $isUid = filter_var($this->request->getPost('is_uid'), FILTER_VALIDATE_BOOL);

        if ($year1 < 2000 || $year2 < 2000 || $unitId <= 0) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Parameter tidak valid.',
            ]);
        }

        $data1 = $this->fetchDataForYear($year1, $unitId, $isUid);
        $data2 = $this->fetchDataForYear($year2, $unitId, $isUid);

        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        return $this->response->setJSON([
            'status' => 'success',
            'data1' => $data1,
            'data2' => $data2,
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchDataForYear(int $year, int $unitId, bool $isUid): array
    {
        if ($isUid || $unitId === 54000) {
            return $this->susutModel->getSusutUidRowsByYear($year);
        }

        return array_values(array_filter(
            $this->susutModel->getSusutRowsByYear($year),
            static fn(array $row): bool => (int) ($row['unit_id'] ?? 0) === $unitId
        ));
    }

    private function normalizeTargetValue(mixed $raw): float
    {
        if (is_string($raw)) {
            $value = trim($raw);
            if ($value === '') {
                return 0.0;
            }

            $value = str_replace('%', '', $value);
            $value = str_replace(',', '.', $value);
            $raw = is_numeric($value) ? (float) $value : 0.0;
        }

        $number = is_numeric($raw) ? (float) $raw : 0.0;

        // Excel percentage cells can come as 0.0588 for 5.88%.
        if ($number > -1 && $number < 1) {
            $number *= 100;
        }

        return round($number, 2);
    }

    private function normalizeUnitName(string $value): string
    {
        $normalized = strtoupper(trim($value));
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? $normalized;

        return $normalized;
    }

    private function writeSpreadsheetToString(Xlsx $writer): string
    {
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return is_string($content) ? $content : '';
    }
}
