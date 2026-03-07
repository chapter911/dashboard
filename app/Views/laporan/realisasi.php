<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$type = (string) ($type ?? 'tahunan');
$params = is_array($params ?? null) ? $params : [];
$sort = (string) ($sort ?? 'none');
$rows = is_array($rows ?? null) ? $rows : [];

$modeLabel = [
    'tahunan' => 'Tahunan',
    'bulanan' => 'Bulanan',
    'harian' => 'Harian',
][$type] ?? 'Tahunan';

$monthNames = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember',
];

$currentYear = (int) date('Y');
$selectedTahun = (int) ($params['tahun'] ?? $currentYear);
$selectedBulan = (int) ($params['bulan'] ?? (int) date('n'));
?>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h5 class="mb-0">Filter Realisasi</h5>
            <small class="text-muted">Analisa pencapaian target penggantian meter berdasarkan periode.</small>
        </div>
        <span class="badge bg-label-primary">Mode: <span id="modeBadgeText"><?= esc($modeLabel) ?></span></span>
    </div>
    <div class="card-body">
        <form method="post" action="<?= site_url('C_Laporan/Realisasi/data') ?>" class="row g-3" id="realisasiFilterForm">
            <?= csrf_field() ?>
            <div class="col-md-3">
                <label class="form-label">Mode</label>
                <select class="form-select" name="type" id="type_realisasi">
                    <option value="tahunan" <?= $type === 'tahunan' ? 'selected' : '' ?>>Tahunan</option>
                    <option value="bulanan" <?= $type === 'bulanan' ? 'selected' : '' ?>>Bulanan</option>
                    <option value="harian" <?= $type === 'harian' ? 'selected' : '' ?>>Harian</option>
                </select>
            </div>
            <div class="col-md-3" id="filter_tahun_wrap">
                <label class="form-label">Tahun</label>
                <select class="form-select" name="tahun" id="filter_tahun">
                    <?php for ($year = $currentYear + 1; $year >= $currentYear - 5; $year--): ?>
                        <option value="<?= esc((string) $year) ?>" <?= $selectedTahun === $year ? 'selected' : '' ?>><?= esc((string) $year) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2" id="filter_bulan">
                <label class="form-label">Bulan</label>
                <select class="form-select" name="bulan" id="filter_bulan_select">
                    <?php foreach ($monthNames as $monthNumber => $monthLabel): ?>
                        <option value="<?= esc((string) $monthNumber) ?>" <?= $selectedBulan === $monthNumber ? 'selected' : '' ?>>
                            <?= esc($monthLabel) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2" id="filter_tgl">
                <label class="form-label">Tanggal</label>
                <input class="form-control" type="date" name="tgl" id="filter_tgl_input" value="<?= esc((string) ($params['tgl'] ?? date('Y-m-d'))) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Urutkan</label>
                <select class="form-select" name="sort" id="filter_sort">
                    <option value="none" <?= $sort === 'none' ? 'selected' : '' ?>>Default</option>
                    <option value="highest" <?= $sort === 'highest' ? 'selected' : '' ?>>Persentase Tertinggi</option>
                    <option value="lowest" <?= $sort === 'lowest' ? 'selected' : '' ?>>Persentase Terendah</option>
                </select>
            </div>
        </form>
    </div>
</div>

<div id="realisasiContentContainer">
    <?= view('laporan/realisasi_content', [
        'params' => $params,
        'rows' => $rows,
    ]) ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/vendor/libs/apex-charts/apexcharts.js') ?>"></script>
<script>
  (function () {
    const $form = $('#realisasiFilterForm');
    const $container = $('#realisasiContentContainer');
    const $mode = $('#type_realisasi');
    const $tahunWrap = $('#filter_tahun_wrap');
    const $bulanWrap = $('#filter_bulan');
    const $tglWrap = $('#filter_tgl');
    const csrfFieldName = '<?= esc(csrf_token()) ?>';

    window.realisasiCharts = window.realisasiCharts || {
      compare: null,
      ranking: null
    };

    function destroyCharts() {
      if (window.realisasiCharts.compare) {
        window.realisasiCharts.compare.destroy();
        window.realisasiCharts.compare = null;
      }
      if (window.realisasiCharts.ranking) {
        window.realisasiCharts.ranking.destroy();
        window.realisasiCharts.ranking = null;
      }
    }

    function syncFilter() {
      const mode = $mode.val();
      $tahunWrap.toggle(mode !== 'harian');
      $bulanWrap.toggle(mode === 'bulanan');
      $tglWrap.toggle(mode === 'harian');

      let modeText = 'Tahunan';
      if (mode === 'bulanan') {
        modeText = 'Bulanan';
      } else if (mode === 'harian') {
        modeText = 'Harian';
      }
      $('#modeBadgeText').text(modeText);
    }

    function applyCsrfToken(token) {
      if (!token) {
        return;
      }
      $form.find('input[name="' + csrfFieldName + '"]').val(token);
    }

    function initRealisasiCharts() {
      destroyCharts();

      const dataEl = document.getElementById('realisasiChartData');
      if (!dataEl || typeof ApexCharts === 'undefined') {
        return;
      }

      let payload = {};
      try {
        payload = JSON.parse(dataEl.textContent || '{}');
      } catch (e) {
        payload = {};
      }

      const labels = Array.isArray(payload.labels) ? payload.labels : [];
      const targetSeries = Array.isArray(payload.target) ? payload.target : [];
      const realisasiSeries = Array.isArray(payload.realisasi) ? payload.realisasi : [];
      const rankLabels = Array.isArray(payload.rankLabels) ? payload.rankLabels : [];
      const rankPercent = Array.isArray(payload.rankPercent) ? payload.rankPercent : [];

      if (labels.length > 0 && document.querySelector('#chartRealisasiCompare')) {
        window.realisasiCharts.compare = new ApexCharts(document.querySelector('#chartRealisasiCompare'), {
          chart: {
            type: 'bar',
            height: 340,
            toolbar: {
              show: true
            }
          },
          series: [
            {
              name: 'Target',
              data: targetSeries
            },
            {
              name: 'Realisasi',
              data: realisasiSeries
            }
          ],
          xaxis: {
            categories: labels,
            labels: {
              rotate: -35
            }
          },
          yaxis: {
            title: {
              text: 'Jumlah'
            }
          },
          plotOptions: {
            bar: {
              borderRadius: 4,
              columnWidth: '60%'
            }
          },
          colors: ['#0a66c2', '#2ab784'],
          legend: {
            position: 'top'
          },
          dataLabels: {
            enabled: false
          }
        });
        window.realisasiCharts.compare.render();
      }

      if (rankLabels.length > 0 && document.querySelector('#chartRealisasiRanking')) {
        window.realisasiCharts.ranking = new ApexCharts(document.querySelector('#chartRealisasiRanking'), {
          chart: {
            type: 'bar',
            height: 340,
            toolbar: {
              show: false
            }
          },
          series: [
            {
              name: 'Persentase',
              data: rankPercent
            }
          ],
          xaxis: {
            categories: rankLabels,
            labels: {
              formatter: function (val) {
                return val + '%';
              }
            }
          },
          plotOptions: {
            bar: {
              horizontal: true,
              borderRadius: 4,
              barHeight: '55%'
            }
          },
          colors: ['#f59e0b'],
          dataLabels: {
            enabled: true,
            formatter: function (val) {
              return Number(val).toFixed(2) + '%';
            }
          },
          tooltip: {
            y: {
              formatter: function (val) {
                return Number(val).toFixed(2) + '%';
              }
            }
          }
        });
        window.realisasiCharts.ranking.render();
      }
    }

    function loadRealisasiData() {
      $.ajax({
        url: '<?= site_url('C_Laporan/Realisasi/data') ?>',
        type: 'POST',
        data: $form.serialize(),
        beforeSend: function () {
          if (typeof Swal !== 'undefined') {
            Swal.fire({
              title: 'Mohon Tunggu',
              html: 'Mengambil data realisasi...',
              allowOutsideClick: false,
              showConfirmButton: false,
              didOpen: function () {
                Swal.showLoading();
              }
            });
          }
        },
        success: function (response, _textStatus, xhr) {
          applyCsrfToken(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
          $container.html(response);
          initRealisasiCharts();
        },
        error: function (xhr) {
          applyCsrfToken(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
          if (typeof Swal !== 'undefined') {
            Swal.fire('Gagal', 'Gagal memuat data realisasi.', 'error');
          }
        },
        complete: function () {
          if (typeof Swal !== 'undefined') {
            Swal.close();
          }
        }
      });
    }

    $form.on('submit', function (e) {
      e.preventDefault();
      loadRealisasiData();
    });

    $mode.on('change', function () {
      syncFilter();
      loadRealisasiData();
    });

    $('#filter_tahun, #filter_bulan_select, #filter_tgl_input, #filter_sort').on('change', function () {
      loadRealisasiData();
    });

    syncFilter();
    initRealisasiCharts();
  })();
</script>
<?= $this->endSection() ?>
