<?php

namespace App\Controllers;

use App\Models\DashboardModel;
use App\Models\SusutModel;
use CodeIgniter\HTTP\ResponseInterface;

class Dashboard extends BaseController
{
    private DashboardModel $dashboardModel;
    private SusutModel $susutModel;

    public function __construct()
    {
        $this->dashboardModel = new DashboardModel();
        $this->susutModel = new SusutModel();
    }

    public function index(): string
    {
        $year = (int) date('Y');
        $startYearDate = $year . '-01-01';
        $today = date('Y-m-d');
        $currentMonth = (int) date('n');
        $monthEnd = date('Y-m-t');

        $susutBulanan = $this->susutModel->getDashboardSusutBulanan($year, $currentMonth, 'netto', 54000);
        $susutKumulatif = $this->susutModel->getDashboardSusutKumulatif($year, $currentMonth, 'netto', 54000);

        if ($susutBulanan === null || $susutKumulatif === null) {
            $susutRows = $this->susutModel->getSusutUidRowsByYear($year);

            foreach ($susutRows as $row) {
                $month = (int) date('n', strtotime((string) ($row['periode'] ?? '')));
                if ($month !== $currentMonth) {
                    continue;
                }

                $susutBulanan = $susutBulanan ?? (float) ($row['persentase'] ?? 0);
                $susutKumulatif = $susutKumulatif ?? (float) ($row['akumulasi_persentase'] ?? 0);
                break;
            }
        }

        // Preserve legacy baseline when data source is unavailable.
        $susutBulanan = $susutBulanan ?? 4.9;
        $susutKumulatif = $susutKumulatif ?? 5.2;

        return view('dashboard/index', [
            'title' => 'Dashboard PLN',
            'pageHeading' => 'Dashboard PLN',
            'p2tlTahunan' => $this->dashboardModel->getAkumulasiTahunan($startYearDate, $today, '1'),
            'p2tlBulanan' => $this->dashboardModel->getAkumulasiBulanan($year, $currentMonth, '1'),
            'temuanTahunan' => $this->dashboardModel->getTemuanTahunan($year),
            'hitrate' => $this->dashboardModel->getHitrateRange($startYearDate, $monthEnd, '1'),
            'performance' => $this->dashboardModel->getAkumulasiTahunan($startYearDate, $today, '1'),
            'currentYear' => $year,
            'susutBulanan' => $susutBulanan,
            'susutKumulatif' => $susutKumulatif,
        ]);
    }

    public function getChartIndex(): ResponseInterface
    {
        $jenisAkumulasi = strtoupper((string) ($this->request->getPost('jenis_akumulasi') ?? 'TAHUNAN'));
        $sortir = (string) ($this->request->getPost('sortir') ?? '1');

        $data = [];
        if ($jenisAkumulasi === 'BULANAN') {
            $month = (string) ($this->request->getPost('bulan') ?? date('Y-m'));
            $monthStart = preg_match('/^\d{4}-\d{2}$/', $month) === 1 ? $month . '-01' : date('Y-m-01');
            $monthEnd = date('Y-m-t', strtotime($monthStart));
            $data = $this->dashboardModel->getAkumulasiTahunan($monthStart, $monthEnd, $sortir);
        } elseif ($jenisAkumulasi === 'HARIAN') {
            $tanggal = (string) ($this->request->getPost('tanggal') ?? date('Y-m-d'));
            $isValidDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) === 1;
            $date = $isValidDate ? $tanggal : date('Y-m-d');
            $data = $this->dashboardModel->getAkumulasiTahunan($date, $date, $sortir);
        } else {
            $start = (string) ($this->request->getPost('bulan_awal') ?? date('Y-01-01'));
            $end = (string) ($this->request->getPost('bulan_akhir') ?? date('Y-m-d'));
            $startDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $start) === 1 ? $start : date('Y-01-01');
            $endDate = preg_match('/^\d{4}-\d{2}-\d{2}$/', $end) === 1 ? $end : date('Y-m-d');
            $data = $this->dashboardModel->getAkumulasiTahunan($startDate, $endDate, $sortir);
        }

        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        return $this->response->setBody(view('dashboard/ajax_index_chart', [
            'data' => $data,
        ]));
    }

    public function getChartHitrate(): ResponseInterface
    {
        $tanggalAwal = (string) ($this->request->getPost('tanggal_awal') ?? date('Y-m-01'));
        $tanggalAkhir = (string) ($this->request->getPost('tanggal_akhir') ?? date('Y-m-t'));
        $sortir = (string) ($this->request->getPost('sortir_hitrate') ?? '1');

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalAwal) !== 1) {
            $tanggalAwal = date('Y-m-01');
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalAkhir) !== 1) {
            $tanggalAkhir = date('Y-m-t');
        }

        $rows = $this->dashboardModel->getHitrateRange($tanggalAwal, $tanggalAkhir, $sortir);

        $this->response->setHeader('X-CSRF-TOKEN', csrf_hash());

        return $this->response->setBody(view('dashboard/ajax_hitrate_chart', [
            'data' => $rows,
        ]));
    }
}
