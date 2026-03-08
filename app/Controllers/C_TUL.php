<?php

namespace App\Controllers;

use App\Models\TulModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class C_TUL extends BaseController
{
    private TulModel $tulModel;

    public function __construct()
    {
        $this->tulModel = new TulModel();
    }

    public function index(): string
    {
        $groupId = (int) (session('group_id') ?? 0);
        $userUnitId = (int) (session('unit_id') ?? 0);

        return view('tul/index', [
            'title' => 'Data TUL',
            'pageHeading' => 'Data TUL',
            'userGroupId' => $groupId,
            'selectedUnitId' => $userUnitId,
            'units' => $groupId === 1 ? $this->tulModel->getUnits() : [],
            'currentPeriod' => date('Y-m'),
        ]);
    }

    public function data(): ResponseInterface
    {
        $draw = (int) ($this->request->getPost('draw') ?? 0);
        $start = max(0, (int) ($this->request->getPost('start') ?? 0));
        $length = (int) ($this->request->getPost('length') ?? 10);
        if ($length < -1) {
            $length = 10;
        }

        $filters = [
            'periode' => (string) ($this->request->getPost('periode') ?? ''),
            'unit_id' => (string) ($this->request->getPost('unit_id') ?? ''),
        ];

        $search = (string) ($this->request->getPost('search')['value'] ?? '');
        $order = $this->request->getPost('order')[0] ?? null;

        $isAdmin = (int) (session('group_id') ?? 0) === 1;
        $userUnitId = session('unit_id') !== null ? (int) session('unit_id') : null;

        $rows = $this->tulModel->getDatatableRows($filters, $start, $length, $search, is_array($order) ? $order : null, $isAdmin, $userUnitId);

        $formatted = array_map(function (array $row): array {
            return [
                'id' => (int) ($row['id'] ?? 0),
                'periode' => (string) ($row['periode'] ?? ''),
                'tarif' => (string) ($row['tarif'] ?? ''),
                'pelanggan' => (float) ($row['pelanggan'] ?? 0),
                'daya' => (float) ($row['daya'] ?? 0),
                'pemakaian_jumlah' => (float) ($row['pemakaian_jumlah'] ?? 0),
                'pemakaian_lwbp' => (float) ($row['pemakaian_lwbp'] ?? 0),
                'pemakaian_wbp' => (float) ($row['pemakaian_wbp'] ?? 0),
                'pemakaian_kelebihan_kvarh' => (float) ($row['pemakaian_kelebihan_kvarh'] ?? 0),
                'biaya_beban' => (float) ($row['biaya_beban'] ?? 0),
                'biaya_kwh' => (float) ($row['biaya_kwh'] ?? 0),
                'biaya_kelebihan_kvarh' => (float) ($row['biaya_kelebihan_kvarh'] ?? 0),
                'biaya_ttlb' => (float) ($row['biaya_ttlb'] ?? 0),
                'jumlah' => (float) ($row['jumlah'] ?? 0),
                'created_by' => (string) ($row['created_by'] ?? ''),
                'created_at' => (string) ($row['created_at'] ?? ''),
                'unit_id' => (int) ($row['unit_id'] ?? 0),
                'unit_name' => (string) ($row['unit_name'] ?? ''),
            ];
        }, $rows);

        $recordsFiltered = $this->tulModel->countFiltered($filters, $search, $isAdmin, $userUnitId);
        $recordsTotal = $this->tulModel->countTotalByScope($filters, $isAdmin, $userUnitId);

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
            return redirect()->to(site_url('C_TUL'))->with('error', 'Periode upload tidak valid.');
        }

        $groupId = (int) (session('group_id') ?? 0);
        $userUnitId = (int) (session('unit_id') ?? 0);

        $uploadUnitId = $groupId === 1
            ? (int) ($this->request->getPost('unit_id') ?? 0)
            : $userUnitId;

        if ($uploadUnitId <= 0) {
            return redirect()->to(site_url('C_TUL'))->with('error', 'Unit untuk upload belum dipilih.');
        }

        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return redirect()->to(site_url('C_TUL'))->with('error', 'File upload tidak valid.');
        }

        $extension = strtolower((string) $file->getClientExtension());
        if (! in_array($extension, ['xlsx', 'xls'], true)) {
            return redirect()->to(site_url('C_TUL'))->with('error', 'Format file harus .xlsx atau .xls.');
        }

        if ($file->getSizeByUnit('mb') > 10) {
            return redirect()->to(site_url('C_TUL'))->with('error', 'Ukuran file maksimal 10 MB.');
        }

        $tempName = $file->getRandomName();
        $targetPath = WRITEPATH . 'uploads/' . $tempName;

        try {
            $file->move(WRITEPATH . 'uploads', $tempName);
            $spreadsheet = IOFactory::load($targetPath);
            $sheet = $spreadsheet->getActiveSheet();

            $rows = [];
            $createdBy = (string) (session('username') ?? 'system');
            $skipRows = [27, 44, 55, 66, 75];

            for ($row = 17; $row <= 83; $row++) {
                if (in_array($row, $skipRows, true)) {
                    continue;
                }

                $tarif = trim((string) $sheet->getCell('B' . $row)->getFormattedValue());
                if ($tarif === '') {
                    continue;
                }

                $rows[] = [
                    'periode' => $periodDate,
                    'unit_id' => $uploadUnitId,
                    'tarif' => $tarif,
                    'pelanggan' => $this->toNumber($sheet->getCell('F' . $row)->getCalculatedValue()),
                    'daya' => $this->toNumber($sheet->getCell('G' . $row)->getCalculatedValue()),
                    'pemakaian_jumlah' => $this->toNumber($sheet->getCell('H' . $row)->getCalculatedValue()),
                    'pemakaian_lwbp' => $this->toNumber($sheet->getCell('I' . $row)->getCalculatedValue()),
                    'pemakaian_wbp' => $this->toNumber($sheet->getCell('J' . $row)->getCalculatedValue()),
                    'pemakaian_kelebihan_kvarh' => $this->toNumber($sheet->getCell('K' . $row)->getCalculatedValue()),
                    'biaya_beban' => $this->toNumber($sheet->getCell('L' . $row)->getCalculatedValue()),
                    'biaya_kwh' => $this->toNumber($sheet->getCell('N' . $row)->getCalculatedValue()),
                    'biaya_kelebihan_kvarh' => $this->toNumber($sheet->getCell('O' . $row)->getCalculatedValue()),
                    'biaya_ttlb' => $this->toNumber($sheet->getCell('P' . $row)->getCalculatedValue()),
                    'jumlah' => $this->toNumber($sheet->getCell('Q' . $row)->getCalculatedValue()),
                    'created_by' => $createdBy,
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }

            if ($rows === []) {
                return redirect()->to(site_url('C_TUL'))->with('error', 'Tidak ada data valid yang dapat diupload dari template.');
            }

            $this->tulModel->replacePeriodUnitData($periodDate, $uploadUnitId, $rows);
        } catch (Throwable $e) {
            log_message('error', 'TUL_UPLOAD_FAILED: {message}', ['message' => $e->getMessage()]);

            return redirect()->to(site_url('C_TUL'))->with('error', 'Upload data TUL gagal diproses.');
        } finally {
            if (is_file($targetPath)) {
                @unlink($targetPath);
            }
        }

        return redirect()->to(site_url('C_TUL'))->with('success', 'Data TUL berhasil diupload.');
    }

    public function summaryPerUnit(): ResponseInterface
    {
        $period = (string) ($this->request->getPost('periode') ?? '');
        $isAdmin = (int) (session('group_id') ?? 0) === 1;
        $userUnitId = session('unit_id') !== null ? (int) session('unit_id') : null;

        $rows = $this->tulModel->getSummaryPerUnit($period, $isAdmin, $userUnitId);

        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        return $this->response->setJSON($rows);
    }

    public function detail(): ResponseInterface
    {
        $id = (int) ($this->request->getPost('id') ?? 0);
        $isAdmin = (int) (session('group_id') ?? 0) === 1;
        $userUnitId = session('unit_id') !== null ? (int) session('unit_id') : null;

        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['message' => 'ID tidak valid']);
        }

        $row = $this->tulModel->findByIdScoped($id, $isAdmin, $userUnitId);
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
            'tarif' => trim((string) ($this->request->getPost('tarif') ?? '')),
            'pelanggan' => $this->toNumber($this->request->getPost('pelanggan')),
            'daya' => $this->toNumber($this->request->getPost('daya')),
            'pemakaian_jumlah' => $this->toNumber($this->request->getPost('pemakaian_jumlah')),
            'pemakaian_lwbp' => $this->toNumber($this->request->getPost('pemakaian_lwbp')),
            'pemakaian_wbp' => $this->toNumber($this->request->getPost('pemakaian_wbp')),
            'pemakaian_kelebihan_kvarh' => $this->toNumber($this->request->getPost('pemakaian_kelebihan_kvarh')),
            'biaya_beban' => $this->toNumber($this->request->getPost('biaya_beban')),
            'biaya_kwh' => $this->toNumber($this->request->getPost('biaya_kwh')),
            'biaya_kelebihan_kvarh' => $this->toNumber($this->request->getPost('biaya_kelebihan_kvarh')),
            'biaya_ttlb' => $this->toNumber($this->request->getPost('biaya_ttlb')),
            'jumlah' => $this->toNumber($this->request->getPost('jumlah')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($payload['tarif'] === '') {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Tarif wajib diisi']);
        }

        $isAdmin = (int) (session('group_id') ?? 0) === 1;
        $userUnitId = session('unit_id') !== null ? (int) session('unit_id') : null;

        $ok = $this->tulModel->updateByIdScoped($id, $payload, $isAdmin, $userUnitId);
        if (! $ok) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Gagal mengubah data TUL']);
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

        $isAdmin = (int) (session('group_id') ?? 0) === 1;
        $userUnitId = session('unit_id') !== null ? (int) session('unit_id') : null;

        $ok = $this->tulModel->deleteByIdScoped($id, $isAdmin, $userUnitId);
        if (! $ok) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data TUL']);
        }

        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        return $this->response->setJSON(['status' => 'success']);
    }

    public function dashboard(): string
    {
        $groupId = (int) (session('group_id') ?? 0);

        return view('tul/dashboard', [
            'title' => 'Dashboard TUL',
            'pageHeading' => 'Dashboard TUL',
            'userGroupId' => $groupId,
            'units' => $groupId === 1 ? $this->tulModel->getUnits() : [],
            'golonganTarif' => $this->tulModel->getGolonganTarifList(),
            'currentYear' => (int) date('Y'),
        ]);
    }

    public function dashboardData(): ResponseInterface
    {
        $year = (int) ($this->request->getPost('year') ?? date('Y'));
        $unitId = $this->request->getPost('unit_id');
        $golTarif = trim((string) ($this->request->getPost('gol_tarif') ?? ''));

        $isAdmin = (int) (session('group_id') ?? 0) === 1;
        $userUnitId = session('unit_id') !== null ? (int) session('unit_id') : null;

        $rawRows = $this->tulModel->getDashboardData(
            $year,
            is_numeric($unitId) && (int) $unitId > 0 ? (int) $unitId : null,
            $golTarif !== '' ? $golTarif : null,
            $isAdmin,
            $userUnitId
        );

        $grouped = [];
        foreach ($rawRows as $row) {
            $unitName = (string) ($row['unit_name'] ?? '-');
            $gol = (string) ($row['gol_tarif'] ?? '-');
            $daya = (string) ($row['daya'] ?? '-');
            $key = $unitName . '|' . $gol . '|' . $daya;

            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'unit_name' => $unitName,
                    'gol_tarif' => $gol,
                    'daya' => $daya,
                    'months' => array_fill(1, 12, 0.0),
                ];
            }

            $month = (int) date('n', strtotime((string) ($row['periode'] ?? '')));
            $grouped[$key]['months'][$month] = (float) ($row['pemakaian_jumlah'] ?? 0);
        }

        $rows = [];
        $no = 1;
        foreach ($grouped as $group) {
            $rowData = [$no++, $group['unit_name'], $group['gol_tarif'], $group['daya']];
            for ($month = 1; $month <= 12; $month++) {
                $rowData[] = number_format((float) ($group['months'][$month] ?? 0), 0, ',', '.');
            }
            $rows[] = $rowData;
        }

        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        return $this->response->setJSON(['data' => $rows]);
    }

    public function chartData(): ResponseInterface
    {
        $year = (int) ($this->request->getPost('year') ?? date('Y'));
        $unitId = $this->request->getPost('unit_id');
        $golTarif = trim((string) ($this->request->getPost('gol_tarif') ?? ''));

        $isAdmin = (int) (session('group_id') ?? 0) === 1;
        $userUnitId = session('unit_id') !== null ? (int) session('unit_id') : null;

        $chartRows = $this->tulModel->getChartData(
            $year,
            is_numeric($unitId) && (int) $unitId > 0 ? (int) $unitId : null,
            $golTarif !== '' ? $golTarif : null,
            $isAdmin,
            $userUnitId
        );

        $map = [];
        foreach ($chartRows as $row) {
            $map[(int) ($row['month'] ?? 0)] = (float) ($row['total'] ?? 0);
        }

        $series = [];
        for ($month = 1; $month <= 12; $month++) {
            $series[] = $map[$month] ?? 0;
        }

        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        return $this->response->setJSON(['series' => $series]);
    }

    public function grafik(): string
    {
        $groupId = (int) (session('group_id') ?? 0);

        return view('tul/grafik', [
            'title' => 'Grafik TUL',
            'pageHeading' => 'Grafik TUL',
            'userGroupId' => $groupId,
            'units' => $groupId === 1 ? $this->tulModel->getUnits() : [],
            'currentYear' => (int) date('Y'),
        ]);
    }

    public function pieComparisonData(): ResponseInterface
    {
        $periodType = (string) ($this->request->getPost('period_type') ?? 'yearly');
        if (! in_array($periodType, ['yearly', 'monthly'], true)) {
            $periodType = 'yearly';
        }

        $periodLeft = (string) ($this->request->getPost('period_left') ?? date('Y'));
        $periodRight = (string) ($this->request->getPost('period_right') ?? date('Y'));
        $unitId = $this->request->getPost('unit_id');

        $isAdmin = (int) (session('group_id') ?? 0) === 1;
        $userUnitId = session('unit_id') !== null ? (int) session('unit_id') : null;

        $left = $this->tulModel->getPieDataByPeriod(
            $periodLeft,
            $periodType,
            is_numeric($unitId) && (int) $unitId > 0 ? (int) $unitId : null,
            $isAdmin,
            $userUnitId
        );

        $right = $this->tulModel->getPieDataByPeriod(
            $periodRight,
            $periodType,
            is_numeric($unitId) && (int) $unitId > 0 ? (int) $unitId : null,
            $isAdmin,
            $userUnitId
        );

        $format = static function (array $rows): array {
            $categories = [];
            $series = [];
            foreach ($rows as $row) {
                $categories[] = (string) ($row['kategori'] ?? '-');
                $series[] = (float) ($row['total_pemakaian'] ?? 0);
            }

            return ['categories' => $categories, 'series' => $series];
        };

        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        return $this->response->setJSON([
            'left' => $format($left),
            'right' => $format($right),
        ]);
    }

    public function kwhJualTable(): ResponseInterface
    {
        $periodType = (string) ($this->request->getPost('period_type') ?? 'yearly');
        if (! in_array($periodType, ['yearly', 'monthly'], true)) {
            $periodType = 'yearly';
        }

        $periodLeft = (string) ($this->request->getPost('period_left') ?? date('Y'));
        $periodRight = (string) ($this->request->getPost('period_right') ?? date('Y'));
        $unitId = $this->request->getPost('unit_id');

        $isAdmin = (int) (session('group_id') ?? 0) === 1;
        $userUnitId = session('unit_id') !== null ? (int) session('unit_id') : null;

        $rows = $this->tulModel->getKwhJualComparison(
            $periodLeft,
            $periodRight,
            $periodType,
            is_numeric($unitId) && (int) $unitId > 0 ? (int) $unitId : null,
            $isAdmin,
            $userUnitId
        );

        $table = [];
        $totalLeft = 0.0;
        $totalRight = 0.0;

        foreach ($rows as $row) {
            $left = (float) ($row['kwh_left'] ?? 0);
            $right = (float) ($row['kwh_right'] ?? 0);
            $growth = $left > 0 ? (($right - $left) / $left) * 100 : 0.0;

            $table[] = [
                'segmen' => (string) ($row['kategori'] ?? '-'),
                'kwh_left' => $left,
                'kwh_right' => $right,
                'growth' => $growth,
            ];

            $totalLeft += $left;
            $totalRight += $right;
        }

        $totalGrowth = $totalLeft > 0 ? (($totalRight - $totalLeft) / $totalLeft) * 100 : 0.0;

        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        return $this->response->setJSON([
            'data' => $table,
            'total' => [
                'kwh_left' => $totalLeft,
                'kwh_right' => $totalRight,
                'growth' => $totalGrowth,
            ],
            'period_left' => $periodLeft,
            'period_right' => $periodRight,
            'period_type' => $periodType,
        ]);
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
}
