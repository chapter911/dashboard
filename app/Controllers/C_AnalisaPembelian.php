<?php

namespace App\Controllers;

use App\Models\AnalisaPembelianModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class C_AnalisaPembelian extends BaseController
{
    private AnalisaPembelianModel $analisaModel;

    /**
     * @var array<string, string>
     */
    private array $excelMap = [
        'menteng' => 'E',
        'bandengan' => 'F',
        'cempaka_putih' => 'G',
        'jati_negara' => 'H',
        'pondok_kopi' => 'I',
        'tanjung_priok' => 'J',
        'marunda' => 'K',
        'bulungan' => 'L',
        'bintaro' => 'M',
        'kebun_jeruk' => 'N',
        'ciputat' => 'O',
        'kramat_jati' => 'P',
        'lenteng_agung' => 'Q',
        'pondok_gede' => 'R',
        'ciracas' => 'S',
        'cengkareng' => 'T',
        'uid' => 'U',
    ];

    public function __construct()
    {
        $this->analisaModel = new AnalisaPembelianModel();
    }

    public function index(): string
    {
        return view('analisa_pembelian/index', [
            'title' => 'Data Analisa Pembelian',
            'pageHeading' => 'Data Analisa Pembelian',
            'currentPeriod' => date('Y-m'),
        ]);
    }

    public function data(): ResponseInterface
    {
        $draw = (int) ($this->request->getPost('draw') ?? 0);
        $start = max(0, (int) ($this->request->getPost('start') ?? 0));
        $length = (int) ($this->request->getPost('length') ?? -1);
        if ($length < -1) {
            $length = 10;
        }

        $filters = [
            'periode' => (string) ($this->request->getPost('periode') ?? ''),
            'metode' => (string) ($this->request->getPost('metode') ?? ''),
        ];

        $search = (string) ($this->request->getPost('search')['value'] ?? '');

        $rows = $this->analisaModel->getDatatableRows($filters, $start, $length, $search);

        $formatted = array_map(function (array $row): array {
            $payload = [
                'id' => (int) ($row['id'] ?? 0),
                'periode' => (string) ($row['periode'] ?? ''),
                'metode' => (string) ($row['metode'] ?? ''),
                'urutan' => (string) ($row['urutan'] ?? ''),
                'hubungan' => (string) ($row['hubungan'] ?? ''),
                'unit_id' => (int) ($row['unit_id'] ?? 0),
                'created_by' => (string) ($row['created_by'] ?? ''),
                'created_at' => (string) ($row['created_at'] ?? ''),
            ];

            foreach (array_keys($this->excelMap) as $key) {
                $payload[$key] = $this->toNumber($row[$key] ?? 0);
            }

            return $payload;
        }, $rows);

        $recordsFiltered = $this->analisaModel->countFiltered($filters, $search);
        $recordsTotal = $this->analisaModel->countTotal($filters);

        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $formatted,
        ]);
    }

    public function upload(): RedirectResponse
    {
        $file = $this->request->getFile('excel_file');
        $period = (string) ($this->request->getPost('periode') ?? '');
        $periodDate = $this->normalizePeriodDate($period);

        if ($periodDate === null) {
            return redirect()->to(site_url('C_AnalisaPembelian'))->with('error', 'Periode upload tidak valid.');
        }

        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return redirect()->to(site_url('C_AnalisaPembelian'))->with('error', 'File upload tidak valid.');
        }

        $extension = strtolower((string) $file->getClientExtension());
        if (! in_array($extension, ['xlsx', 'xls'], true)) {
            return redirect()->to(site_url('C_AnalisaPembelian'))->with('error', 'Format file harus .xlsx atau .xls.');
        }

        if ($file->getSizeByUnit('mb') > 10) {
            return redirect()->to(site_url('C_AnalisaPembelian'))->with('error', 'Ukuran file maksimal 10 MB.');
        }

        $tempName = $file->getRandomName();
        $targetPath = WRITEPATH . 'uploads/' . $tempName;

        try {
            $file->move(WRITEPATH . 'uploads', $tempName);
            $spreadsheet = IOFactory::load($targetPath);
            $sheet = $spreadsheet->getActiveSheet();

            $rows = [];
            $createdBy = (string) (session('username') ?? 'system');
            $skipRows = [28, 29, 30, 50];

            for ($row = 5; $row <= 51; $row++) {
                if (in_array($row, $skipRows, true)) {
                    continue;
                }

                $metode = 'penerimaan';
                if ($row >= 31 && $row <= 49) {
                    $metode = 'pengiriman';
                }
                if ($row === 51) {
                    $metode = 'netto';
                }

                $urutan = trim((string) $sheet->getCell('B' . $row)->getFormattedValue());
                $hubungan = trim((string) $sheet->getCell('C' . $row)->getFormattedValue());
                if ($urutan === '' && $hubungan === '') {
                    continue;
                }

                $payload = [
                    'periode' => $periodDate,
                    'metode' => $metode,
                    'urutan' => $urutan,
                    'hubungan' => $hubungan,
                    'unit_id' => $this->toIntOrNull($sheet->getCell('D' . $row)->getCalculatedValue()),
                    'created_by' => $createdBy,
                    'created_at' => date('Y-m-d H:i:s'),
                ];

                foreach ($this->excelMap as $field => $column) {
                    $payload[$field] = $this->toIntOrNull($sheet->getCell($column . $row)->getCalculatedValue());
                }

                $rows[] = $payload;
            }

            if ($rows === []) {
                return redirect()->to(site_url('C_AnalisaPembelian'))->with('error', 'Tidak ada data valid yang dapat diupload dari template.');
            }

            $this->analisaModel->replacePeriodData($periodDate, $rows);
        } catch (Throwable $e) {
            log_message('error', 'ANALISA_PEMBELIAN_UPLOAD_FAILED: {message}', ['message' => $e->getMessage()]);

            return redirect()->to(site_url('C_AnalisaPembelian'))->with('error', 'Upload data Analisa Pembelian gagal diproses.');
        } finally {
            if (is_file($targetPath)) {
                @unlink($targetPath);
            }
        }

        return redirect()->to(site_url('C_AnalisaPembelian'))->with('success', 'Data Analisa Pembelian berhasil diupload.');
    }

    public function detail(): ResponseInterface
    {
        $id = (int) ($this->request->getPost('id') ?? 0);

        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['message' => 'ID tidak valid']);
        }

        $row = $this->analisaModel->findById($id);
        if (! is_array($row)) {
            return $this->response->setStatusCode(404)->setJSON(['message' => 'Data tidak ditemukan']);
        }

        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        return $this->response->setJSON($row);
    }

    public function update(): ResponseInterface
    {
        $id = (int) ($this->request->getPost('id') ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'ID tidak valid']);
        }

        $payload = [
            'urutan' => trim((string) ($this->request->getPost('urutan') ?? '')),
            'hubungan' => trim((string) ($this->request->getPost('hubungan') ?? '')),
            'unit_id' => $this->toIntOrNull($this->request->getPost('unit_id')),
            'updated_by' => (string) (session('username') ?? 'system'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        foreach (array_keys($this->excelMap) as $field) {
            $payload[$field] = $this->toIntOrNull($this->request->getPost($field));
        }

        if ($payload['urutan'] === '' && $payload['hubungan'] === '') {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Urutan atau hubungan wajib diisi']);
        }

        $ok = $this->analisaModel->updateById($id, $payload);
        if (! $ok) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Gagal mengubah data Analisa Pembelian']);
        }

        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        return $this->response->setJSON(['status' => 'success']);
    }

    public function delete(): ResponseInterface
    {
        $id = (int) ($this->request->getPost('id') ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'ID tidak valid']);
        }

        $ok = $this->analisaModel->deleteById($id);
        if (! $ok) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data Analisa Pembelian']);
        }

        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        return $this->response->setJSON(['status' => 'success']);
    }

    private function normalizePeriodDate(string $period): ?string
    {
        $value = trim($period);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^\d{4}-\d{2}$/', $value) === 1) {
            return $value . '-01';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1) {
            return $value;
        }

        return null;
    }

    private function toNumber(mixed $value): float
    {
        if (is_string($value)) {
            $normalized = trim($value);
            if ($normalized === '') {
                return 0.0;
            }

            $normalized = str_replace([' ', ','], ['', '.'], $normalized);
            $value = is_numeric($normalized) ? (float) $normalized : 0.0;
        }

        return is_numeric($value) ? (float) $value : 0.0;
    }

    private function toIntOrNull(mixed $value): ?int
    {
        $number = $this->toNumber($value);

        if (abs($number) < 0.0000001) {
            if (is_string($value) && trim($value) === '0') {
                return 0;
            }

            if ($value === 0 || $value === 0.0) {
                return 0;
            }

            return null;
        }

        return (int) round($number);
    }
}
