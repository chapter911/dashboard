<?php

namespace App\Controllers;

use App\Models\PssdModel;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class C_PSSD extends BaseController
{
    private PssdModel $pssdModel;

    public function __construct()
    {
        $this->pssdModel = new PssdModel();
    }

    public function index(): string
    {
        $groupId = (int) (session('group_id') ?? 0);
        $userUnitId = (int) (session('unit_id') ?? 0);

        return view('pssd/index', [
            'title' => 'Data PSSD',
            'pageHeading' => 'Data PSSD',
            'userGroupId' => $groupId,
            'selectedUnitId' => $userUnitId,
            'units' => $this->pssdModel->getUnits(),
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

        $isAdmin = (int) (session('group_id') ?? 0) === 1;
        $userUnitId = session('unit_id') !== null ? (int) session('unit_id') : null;

        $rows = $this->pssdModel->getDatatableRows($filters, $start, $length, $search, $isAdmin, $userUnitId);

        $formatted = array_map(function (array $row): array {
            return [
                'id' => (int) ($row['id'] ?? 0),
                'periode' => (string) ($row['periode'] ?? ''),
                'unit_id' => (int) ($row['unit_id'] ?? 0),
                'unit_name' => (string) ($row['unit_name'] ?? ''),
                'nama_sheet' => (string) ($row['nama_sheet'] ?? ''),
                'jenis_peralatan' => (string) ($row['jenis_peralatan'] ?? ''),
                'daya' => $this->toNumber($row['daya'] ?? 0),
                'jam_nyala' => $this->toNumber($row['jam_nyala'] ?? 0),
                'jumlah' => $this->toNumber($row['jumlah'] ?? 0),
                'total_kwh' => $this->toNumber($row['total_kwh'] ?? 0),
                'created_by' => (string) ($row['created_by'] ?? ''),
                'created_at' => (string) ($row['created_at'] ?? ''),
            ];
        }, $rows);

        $recordsFiltered = $this->pssdModel->countFiltered($filters, $search, $isAdmin, $userUnitId);
        $recordsTotal = $this->pssdModel->countTotalByScope($filters, $isAdmin, $userUnitId);

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
            return redirect()->to(site_url('C_PSSD'))->with('error', 'Periode upload tidak valid.');
        }

        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return redirect()->to(site_url('C_PSSD'))->with('error', 'File upload tidak valid.');
        }

        $extension = strtolower((string) $file->getClientExtension());
        if (! in_array($extension, ['xlsx', 'xls'], true)) {
            return redirect()->to(site_url('C_PSSD'))->with('error', 'Format file harus .xlsx atau .xls.');
        }

        if ($file->getSizeByUnit('mb') > 10) {
            return redirect()->to(site_url('C_PSSD'))->with('error', 'Ukuran file maksimal 10 MB.');
        }

        $isAdmin = (int) (session('group_id') ?? 0) === 1;
        $userUnitId = (int) (session('unit_id') ?? 0);
        $selectedUnitId = (int) ($this->request->getPost('unit_id') ?? 0);

        $allUnits = $this->pssdModel->getUnits();
        $targetUnits = [];

        foreach ($allUnits as $unit) {
            $unitId = (int) ($unit['unit_id'] ?? 0);
            if ($unitId <= 0) {
                continue;
            }

            if (! $isAdmin && $unitId !== $userUnitId) {
                continue;
            }

            if ($isAdmin && $selectedUnitId > 0 && $unitId !== $selectedUnitId) {
                continue;
            }

            $targetUnits[] = $unit;
        }

        if ($targetUnits === []) {
            return redirect()->to(site_url('C_PSSD'))->with('error', 'Unit upload tidak valid.');
        }

        $tempName = $file->getRandomName();
        $targetPath = WRITEPATH . 'uploads/' . $tempName;

        try {
            $file->move(WRITEPATH . 'uploads', $tempName);
            $spreadsheet = IOFactory::load($targetPath);

            $rows = [];
            $createdBy = (string) (session('username') ?? 'system');
            $unitIds = [];
            $missingSheets = [];

            foreach ($targetUnits as $unit) {
                $unitId = (int) ($unit['unit_id'] ?? 0);
                $sheetName = trim((string) ($unit['unit_singkatan'] ?? ''));
                $unitName = trim((string) ($unit['unit_name'] ?? ''));

                if ($sheetName === '') {
                    continue;
                }

                if (! $spreadsheet->sheetNameExists($sheetName)) {
                    $missingSheets[] = $sheetName;
                    continue;
                }

                $unitIds[] = $unitId;
                $sheet = $spreadsheet->getSheetByName($sheetName);

                for ($row = 2; $row <= 34; $row++) {
                    $namaSheet = trim((string) $sheet->getCell('B' . $row)->getFormattedValue());
                    $jenisPeralatan = trim((string) $sheet->getCell('C' . $row)->getFormattedValue());

                    if ($namaSheet === '' && $jenisPeralatan === '') {
                        continue;
                    }

                    $rows[] = [
                        'periode' => $periodDate,
                        'unit_id' => $unitId,
                        'nama_sheet' => $namaSheet !== '' ? $namaSheet : $unitName,
                        'jenis_peralatan' => $jenisPeralatan,
                        'daya' => (string) $this->toNumber($sheet->getCell('D' . $row)->getCalculatedValue()),
                        'jam_nyala' => (string) $this->toNumber($sheet->getCell('E' . $row)->getCalculatedValue()),
                        'jumlah' => (string) $this->toNumber($sheet->getCell('F' . $row)->getCalculatedValue()),
                        'total_kwh' => (string) $this->toNumber($sheet->getCell('G' . $row)->getCalculatedValue()),
                        'created_by' => $createdBy,
                        'created_at' => date('Y-m-d'),
                    ];
                }
            }

            if ($rows === []) {
                $msg = $missingSheets === []
                    ? 'Tidak ada data valid yang dapat diupload dari template.'
                    : 'Sheet unit tidak ditemukan pada file: ' . implode(', ', array_unique($missingSheets));

                return redirect()->to(site_url('C_PSSD'))->with('error', $msg);
            }

            $this->pssdModel->replacePeriodUnitsData($periodDate, array_values(array_unique($unitIds)), $rows);
        } catch (Throwable $e) {
            log_message('error', 'PSSD_UPLOAD_FAILED: {message}', ['message' => $e->getMessage()]);

            return redirect()->to(site_url('C_PSSD'))->with('error', 'Upload data PSSD gagal diproses.');
        } finally {
            if (is_file($targetPath)) {
                @unlink($targetPath);
            }
        }

        return redirect()->to(site_url('C_PSSD'))->with('success', 'Data PSSD berhasil diupload.');
    }

    public function summaryPerUnit(): ResponseInterface
    {
        $period = (string) ($this->request->getPost('periode') ?? '');
        $isAdmin = (int) (session('group_id') ?? 0) === 1;
        $userUnitId = session('unit_id') !== null ? (int) session('unit_id') : null;

        $rows = $this->pssdModel->getSummaryPerUnit($period, $isAdmin, $userUnitId);

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

        $row = $this->pssdModel->findByIdScoped($id, $isAdmin, $userUnitId);
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
            'nama_sheet' => trim((string) ($this->request->getPost('nama_sheet') ?? '')),
            'jenis_peralatan' => trim((string) ($this->request->getPost('jenis_peralatan') ?? '')),
            'daya' => (string) $this->toNumber($this->request->getPost('daya')),
            'jam_nyala' => (string) $this->toNumber($this->request->getPost('jam_nyala')),
            'jumlah' => (string) $this->toNumber($this->request->getPost('jumlah')),
            'total_kwh' => (string) $this->toNumber($this->request->getPost('total_kwh')),
        ];

        if ($payload['nama_sheet'] === '' && $payload['jenis_peralatan'] === '') {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Nama sheet atau jenis peralatan wajib diisi']);
        }

        $isAdmin = (int) (session('group_id') ?? 0) === 1;
        $userUnitId = session('unit_id') !== null ? (int) session('unit_id') : null;

        $ok = $this->pssdModel->updateByIdScoped($id, $payload, $isAdmin, $userUnitId);
        if (! $ok) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Gagal mengubah data PSSD']);
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

        $ok = $this->pssdModel->deleteByIdScoped($id, $isAdmin, $userUnitId);
        if (! $ok) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Gagal menghapus data PSSD']);
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
}
