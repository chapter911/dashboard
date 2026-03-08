<?php

namespace App\Controllers;

use App\Models\EminModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class C_Emin extends BaseController
{
    private EminModel $eminModel;

    public function __construct()
    {
        $this->eminModel = new EminModel();
    }

    public function index(): string
    {
        $groupId = (int) (session('group_id') ?? 0);
        $userUnitId = (int) (session('unit_id') ?? 0);

        return view('emin/index', [
            'title' => 'Data EMIN',
            'pageHeading' => 'Data EMIN',
            'userGroupId' => $groupId,
            'selectedUnitId' => $userUnitId,
            'units' => $groupId === 1 ? $this->eminModel->getUnits() : [],
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

        $rows = $this->eminModel->getDatatableRows($filters, $start, $length, $search, is_array($order) ? $order : null, $isAdmin, $userUnitId);

        $formatted = array_map(function (array $row): array {
            return [
                'id' => (int) ($row['id'] ?? 0),
                'periode' => (string) ($row['periode'] ?? ''),
                'periode_rekening' => (string) ($row['periode_rekening'] ?? ''),
                'tarif' => (string) ($row['tarif'] ?? ''),
                'lembar' => (float) ($row['lembar'] ?? 0),
                'pelanggan' => (float) ($row['pelanggan'] ?? 0),
                'emin_awal' => (float) ($row['emin_awal'] ?? 0),
                'kwh_rill' => (float) ($row['kwh_rill'] ?? 0),
                'emin' => (float) ($row['emin'] ?? 0),
                'unit_id' => (int) ($row['unit_id'] ?? 0),
                'unit_name' => (string) ($row['unit_name'] ?? ''),
                'created_by' => (string) ($row['created_by'] ?? ''),
                'created_at' => (string) ($row['created_at'] ?? ''),
            ];
        }, $rows);

        $recordsFiltered = $this->eminModel->countFiltered($filters, $search, $isAdmin, $userUnitId);
        $recordsTotal = $this->eminModel->countTotalByScope($filters, $isAdmin, $userUnitId);

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
        $periodRekening = (string) ($this->request->getPost('periode_rekening') ?? '');

        $periodDate = $this->normalizePeriodDate($period);
        $periodRekeningDate = $this->normalizePeriodDate($periodRekening);

        if ($periodDate === null || $periodRekeningDate === null) {
            return redirect()->to(site_url('C_Emin'))->with('error', 'Periode dan periode rekening wajib valid.');
        }

        $groupId = (int) (session('group_id') ?? 0);
        $userUnitId = (int) (session('unit_id') ?? 0);

        $uploadUnitId = $groupId === 1
            ? (int) ($this->request->getPost('unit_id') ?? 0)
            : $userUnitId;

        if ($uploadUnitId <= 0) {
            return redirect()->to(site_url('C_Emin'))->with('error', 'Unit untuk upload belum dipilih.');
        }

        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return redirect()->to(site_url('C_Emin'))->with('error', 'File upload tidak valid.');
        }

        $extension = strtolower((string) $file->getClientExtension());
        if (! in_array($extension, ['xlsx', 'xls'], true)) {
            return redirect()->to(site_url('C_Emin'))->with('error', 'Format file harus .xlsx atau .xls.');
        }

        if ($file->getSizeByUnit('mb') > 10) {
            return redirect()->to(site_url('C_Emin'))->with('error', 'Ukuran file maksimal 10 MB.');
        }

        $tempName = $file->getRandomName();
        $targetPath = WRITEPATH . 'uploads/' . $tempName;

        try {
            $file->move(WRITEPATH . 'uploads', $tempName);
            $spreadsheet = IOFactory::load($targetPath);
            $sheet = $spreadsheet->getActiveSheet();

            $rows = [];
            $createdBy = (string) (session('username') ?? 'system');

            // Legacy format range: C9 to L52.
            for ($row = 9; $row <= 52; $row++) {
                $tarif = trim((string) $sheet->getCell('C' . $row)->getFormattedValue());
                if ($tarif === '') {
                    continue;
                }

                $rows[] = [
                    'periode' => $periodDate,
                    'periode_rekening' => $periodRekeningDate,
                    'unit_id' => $uploadUnitId,
                    'tarif' => $tarif,
                    'lembar' => $this->toNumber($sheet->getCell('F' . $row)->getCalculatedValue()),
                    'pelanggan' => $this->toNumber($sheet->getCell('G' . $row)->getCalculatedValue()),
                    'emin_awal' => $this->toNumber($sheet->getCell('I' . $row)->getCalculatedValue()),
                    'kwh_rill' => $this->toNumber($sheet->getCell('K' . $row)->getCalculatedValue()),
                    'emin' => $this->toNumber($sheet->getCell('L' . $row)->getCalculatedValue()),
                    'created_by' => $createdBy,
                    'created_at' => date('Y-m-d H:i:s'),
                ];
            }

            if ($rows === []) {
                return redirect()->to(site_url('C_Emin'))->with('error', 'Tidak ada data valid yang dapat diupload dari template.');
            }

            $this->eminModel->replacePeriodUnitData($periodDate, $periodRekeningDate, $uploadUnitId, $rows);
        } catch (Throwable $e) {
            log_message('error', 'EMIN_UPLOAD_FAILED: {message}', ['message' => $e->getMessage()]);

            return redirect()->to(site_url('C_Emin'))->with('error', 'Upload data EMIN gagal diproses.');
        } finally {
            if (is_file($targetPath)) {
                @unlink($targetPath);
            }
        }

        return redirect()->to(site_url('C_Emin'))->with('success', 'Data EMIN berhasil diupload.');
    }

    public function summaryPerUnit(): ResponseInterface
    {
        $period = (string) ($this->request->getPost('periode') ?? '');
        $isAdmin = (int) (session('group_id') ?? 0) === 1;
        $userUnitId = session('unit_id') !== null ? (int) session('unit_id') : null;

        $rows = $this->eminModel->getSummaryPerUnit($period, $isAdmin, $userUnitId);

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

        $row = $this->eminModel->findByIdScoped($id, $isAdmin, $userUnitId);
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
            'lembar' => $this->toNumber($this->request->getPost('lembar')),
            'pelanggan' => $this->toNumber($this->request->getPost('pelanggan')),
            'emin_awal' => $this->toNumber($this->request->getPost('emin_awal')),
            'kwh_rill' => $this->toNumber($this->request->getPost('kwh_rill')),
            'emin' => $this->toNumber($this->request->getPost('emin')),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($payload['tarif'] === '') {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Tarif wajib diisi']);
        }

        $isAdmin = (int) (session('group_id') ?? 0) === 1;
        $userUnitId = session('unit_id') !== null ? (int) session('unit_id') : null;

        $ok = $this->eminModel->updateByIdScoped($id, $payload, $isAdmin, $userUnitId);
        if (! $ok) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Gagal mengubah data EMIN']);
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

        $ok = $this->eminModel->deleteByIdScoped($id, $isAdmin, $userUnitId);
        if (! $ok) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data EMIN']);
        }

        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        return $this->response->setJSON(['status' => 'success']);
    }

    public function dashboard(): string
    {
        $groupId = (int) (session('group_id') ?? 0);

        return view('emin/dashboard', [
            'title' => 'Dashboard EMIN',
            'pageHeading' => 'Dashboard EMIN',
            'userGroupId' => $groupId,
            'units' => $groupId === 1 ? $this->eminModel->getUnits() : [],
            'currentYear' => (int) date('Y'),
        ]);
    }

    public function dashboardData(): ResponseInterface
    {
        $year = (int) ($this->request->getPost('year') ?? date('Y'));
        $unitId = $this->request->getPost('unit_id');

        $isAdmin = (int) (session('group_id') ?? 0) === 1;
        $userUnitId = session('unit_id') !== null ? (int) session('unit_id') : null;

        $rawRows = $this->eminModel->getDashboardData(
            $year,
            is_numeric($unitId) && (int) $unitId > 0 ? (int) $unitId : null,
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
            $grouped[$key]['months'][$month] = (float) ($row['emin'] ?? 0);
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
