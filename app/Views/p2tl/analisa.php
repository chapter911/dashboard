<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$currentYear = (int) ($currentYear ?? date('Y'));
$selectedYear = (int) ($selectedYear ?? $currentYear);
$years = [];
for ($y = $currentYear; $y >= 2020; $y--) {
    $years[] = $y;
}
$userGroupId = (int) ($userGroupId ?? 0);
$selectedUnitId = (int) ($selectedUnitId ?? 0);
$selectedUnitName = (string) ($selectedUnitName ?? '');
?>
<style>
#tableAnalisa thead th {
    text-align: center;
    vertical-align: middle;
    border: 1px solid #fff !important;
}
#tableAnalisa tbody td:nth-child(1),
#tableAnalisa tbody td:nth-child(3),
#tableAnalisa tbody td:nth-child(4),
#tableAnalisa tbody td:nth-child(6),
#tableAnalisa tbody td:nth-child(7),
#tableAnalisa tbody td:nth-child(9),
#tableAnalisa tbody td:nth-child(10),
#tableAnalisa tbody td:nth-child(11),
#tableAnalisa tbody td:nth-child(12) {
    text-align: right;
}
</style>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0\"><?= esc($pageHeading ?? 'Analisa P2TL') ?></h5>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalImportAnalisa">Import Analisa</button>
            <button type="button" class="btn btn-primary" id="btnExportAnalisa">Export Analisa</button>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-2">
                <label class="form-label">Tahun</label>
                <select class="form-select" id="tahun">
                    <?php foreach ($years as $year): ?>
                        <option value="<?= $year ?>" <?= $year === $selectedYear ? 'selected' : '' ?>><?= $year ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Unit</label>
                <?php if ($userGroupId === 1): ?>
                    <select class="form-select" id="unit">
                        <option value="*">SEMUA UNIT</option>
                        <?php foreach (($units ?? []) as $u): ?>
                            <option value="<?= (int) ($u['unit_id'] ?? 0) ?>"><?= esc($u['unit_name'] ?? '-') ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <select class="form-select" id="unit" disabled>
                        <option value="<?= $selectedUnitId ?>"><?= esc($selectedUnitName !== '' ? $selectedUnitName : (string) $selectedUnitId) ?></option>
                    </select>
                <?php endif; ?>
            </div>
            <div class="col-md-3">
                <label class="form-label">IDPEL</label>
                <input type="text" class="form-control" id="idpel" placeholder="Cari IDPEL...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status Temuan</label>
                <select class="form-select" id="temuan_status">
                    <option value="*">Semua Data</option>
                    <option value="has">Ada Temuan</option>
                    <option value="none">Tidak Ada Temuan</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table id="tableAnalisa" class="table table-sm table-striped w-100">
                <thead>
                    <tr>
                        <th>NO</th>
                        <th>IDPEL</th>
                        <th>TARIF</th>
                        <th>DAYA</th>
                        <th>NOMOR GARDU</th>
                        <th>JN RATA-RATA</th>
                        <th>JN RATA-RATA DAYA</th>
                        <th>KONDISI JN RATA-RATA</th>
                        <th>JN MINIMAL</th>
                        <th>JN MAKSIMAL</th>
                        <th>DLPD</th>
                        <th>COUNTING EMIN</th>
                        <th>DETAIL</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalImportAnalisa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Analisa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="<?= site_url('C_P2TL/importAnalisa') ?>" enctype="multipart/form-data" id="formImportAnalisa">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tahun</label>
                            <input type="number" class="form-control" name="tahun" value="<?= (int) ($currentYear ?? date('Y')) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bulan</label>
                            <select class="form-select" name="bulan" required>
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?= $m ?>" <?= $m === (int) date('n') ? 'selected' : '' ?>><?= $m ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Unit</label>
                            <select class="form-select" name="unit_id">
                                <option value="">Ikuti Unit User</option>
                                <?php foreach (($units ?? []) as $u): ?>
                                    <option value="<?= (int) ($u['unit_id'] ?? 0) ?>"><?= esc($u['unit_name'] ?? '-') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">File (xlsx/xls)</label>
                            <input type="file" class="form-control" name="file_import" accept=".xlsx,.xls" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="detailTitle">Detail IDPEL</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3" id="detailMeta">
                    <strong>Tarif:</strong> <span id="detailTarif">-</span>
                    &nbsp; | &nbsp;
                    <strong>Daya:</strong> <span id="detailDaya">-</span>
                </div>
                <div class="mt-3">
                    <canvas id="chartAnalisa" height="120"></canvas>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead id="detailHead"></thead>
                        <tbody id="detailBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    var datatableCssHref = '<?= base_url('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') ?>';
    if (!document.querySelector('link[data-role="datatable-bs5"]')) {
        var styleLink = document.createElement('link');
        styleLink.rel = 'stylesheet';
        styleLink.href = datatableCssHref;
        styleLink.setAttribute('data-role', 'datatable-bs5');
        document.head.appendChild(styleLink);
    }
})();
</script>
<script src="<?= base_url('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') ?>"></script>
<script src="<?= base_url('assets/vendor/libs/chartjs/chartjs.js') ?>"></script>
<script>
(function () {
    if (!window.jQuery || !window.jQuery.fn || typeof window.jQuery.fn.DataTable !== 'function') {
        Swal.fire('Gagal', 'Komponen DataTables belum termuat.', 'error');
        return;
    }

    var csrfFieldName = '<?= esc(csrf_token()) ?>';
    var csrfToken = '<?= esc(csrf_hash()) ?>';
    var chartAnalisa = null;

    var table = $('#tableAnalisa').DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        ajax: {
            url: '<?= site_url('C_P2TL/ajaxAnalisa') ?>',
            type: 'POST',
            data: function (d) {
                d.tahun = $('#tahun').val() || '<?= $selectedYear ?>';
                d.unit = $('#unit').val();
                d.idpel = $('#idpel').val();
                d.temuan_status = $('#temuan_status').val();
                d[csrfFieldName] = csrfToken;
            },
            complete: function (xhr) {
                var fresh = xhr.getResponseHeader('X-CSRF-TOKEN');
                if (fresh) {
                    csrfToken = fresh;
                }
            },
            error: function (xhr) {
                Swal.fire('Gagal', 'Data analisa gagal dimuat (' + xhr.status + ')', 'error');
            }
        },
        order: [[1, 'asc']],
        pageLength: 10
    });

    window.showDetail = function (idpel) {
        $.ajax({
            url: '<?= site_url('C_P2TL/getAnalisaDetailAjax') ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                idpel: idpel,
                tahun: $('#tahun').val(),
                unit: $('#unit').val(),
                [csrfFieldName]: csrfToken
            },
            success: function (response, _status, xhr) {
                var fresh = xhr.getResponseHeader('X-CSRF-TOKEN');
                if (fresh) {
                    csrfToken = fresh;
                }

                $('#detailTitle').text('Detail IDPEL: ' + idpel);
                var years = response.years || [];
                var html = '';

                function formatPercent(value) {
                    if (value === null || Number.isNaN(value) || !Number.isFinite(value)) {
                        return '-';
                    }
                    return value.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + '%';
                }

                function percentDiff(currentValue, baseValue) {
                    if (currentValue === null || baseValue === null || baseValue === 0) {
                        return null;
                    }
                    return ((currentValue - baseValue) / baseValue) * 100;
                }

                // continuousWindowAvg: returns null if out-of-bounds or any value in range is null
                function continuousWindowAvg(series, startIdx, endIdx) {
                    if (startIdx < 0 || endIdx >= series.length) {
                        return null;
                    }
                    var total = 0;
                    var count = 0;
                    for (var i = startIdx; i <= endIdx; i++) {
                        if (series[i] === null) {
                            return null;
                        }
                        total += series[i];
                        count++;
                    }
                    return count > 0 ? (total / count) : null;
                }

                var headHtml = '<tr><th rowspan="2">Bulan</th>';
                headHtml += '<th colspan="' + years.length + '" class="text-center">Pemakaian KWH</th>';
                headHtml += '<th colspan="' + years.length + '" class="text-center">Jam Nyala</th>';
                headHtml += '<th colspan="4" class="text-center">Selisih (%)</th></tr><tr>';

                $.each(years, function (_idx, year) {
                    headHtml += '<th class="text-center">' + year + '</th>';
                });
                $.each(years, function (_idx, year) {
                    headHtml += '<th class="text-center">' + year + '</th>';
                });

                headHtml += '<th class="text-center">Bulanan</th>';
                headHtml += '<th class="text-center">Triwulan</th>';
                headHtml += '<th class="text-center">Semester</th>';
                headHtml += '<th class="text-center">Tahunan</th>';
                headHtml += '</tr>';

                $('#detailHead').html(headHtml);

                if (!response.has_data) {
                    $('#detailTarif').text('-');
                    $('#detailDaya').text('-');
                    html = '<tr><td colspan="' + (1 + (years.length * 2) + 4) + '" class="text-center">Data tidak ditemukan</td></tr>';
                } else {
                    $('#detailTarif').text(response.tarif || '-');
                    $('#detailDaya').text(response.daya === null ? '-' : Number(response.daya).toLocaleString('id-ID', { maximumFractionDigits: 0 }));

                    var currentYear = years.length > 0 ? String(years[0]) : null;
                    var previousYear = years.length > 1 ? String(years[1]) : null;
                    // Continuous 36-month series: [Jan(y-2)..Dec(y-2), Jan(y-1)..Dec(y-1), Jan(y)..Dec(y)]
                    var continuousSeries = Array.isArray(response.jn_continuous)
                        ? response.jn_continuous.map(function (v) { return v === null ? null : Number(v); })
                        : [];
                    while (continuousSeries.length < 36) { continuousSeries.push(null); }

                    $.each(response.rows || [], function (rowIndex, row) {
                        html += '<tr><td>' + row.bulan + '</td>';

                        $.each(years, function (_idx, year) {
                            var kwhData = row.pemakaian_kwh || {};
                            var value = Object.prototype.hasOwnProperty.call(kwhData, String(year)) ? kwhData[String(year)] : null;
                            html += '<td class="text-end">' + (value === null ? '-' : Number(value).toLocaleString('id-ID', { maximumFractionDigits: 0 })) + '</td>';
                        });

                        $.each(years, function (_idx, year) {
                            var nyalaData = row.jam_nyala || {};
                            var value = Object.prototype.hasOwnProperty.call(nyalaData, String(year)) ? nyalaData[String(year)] : null;
                            html += '<td class="text-end">' + (value === null ? '-' : Number(value).toLocaleString('id-ID', { maximumFractionDigits: 0 })) + '</td>';
                        });

                        // baseIdx: position of this month in the 36-month continuous series (rowIndex 0=Jan at offset 24)
                        var baseIdx = 24 + rowIndex;
                        var bulanan = percentDiff(continuousSeries[baseIdx], continuousSeries[baseIdx - 1]);
                        var triwulan = percentDiff(
                            continuousWindowAvg(continuousSeries, baseIdx - 2, baseIdx),
                            continuousWindowAvg(continuousSeries, baseIdx - 5, baseIdx - 3)
                        );
                        var semester = percentDiff(
                            continuousWindowAvg(continuousSeries, baseIdx - 5, baseIdx),
                            continuousWindowAvg(continuousSeries, baseIdx - 11, baseIdx - 6)
                        );
                        var tahunan = percentDiff(
                            continuousWindowAvg(continuousSeries, baseIdx - 11, baseIdx),
                            continuousWindowAvg(continuousSeries, baseIdx - 23, baseIdx - 12)
                        );

                        html += '<td class="text-end">' + formatPercent(bulanan) + '</td>';
                        html += '<td class="text-end">' + formatPercent(triwulan) + '</td>';
                        html += '<td class="text-end">' + formatPercent(semester) + '</td>';
                        html += '<td class="text-end">' + formatPercent(tahunan) + '</td>';
                        html += '</tr>';
                    });
                }

                $('#detailBody').html(html);

                $.ajax({
                    url: '<?= site_url('C_P2TL/getAnalisaGrafikAjax') ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        idpel: idpel,
                        tahun: $('#tahun').val(),
                        unit: $('#unit').val(),
                        [csrfFieldName]: csrfToken
                    },
                    success: function (chartResponse, _chartStatus, chartXhr) {
                        var freshToken = chartXhr.getResponseHeader('X-CSRF-TOKEN');
                        if (freshToken) {
                            csrfToken = freshToken;
                        }

                        var canvas = document.getElementById('chartAnalisa');
                        if (!canvas || typeof window.Chart === 'undefined') {
                            return;
                        }

                        var ctx = canvas.getContext('2d');
                        if (chartAnalisa) {
                            chartAnalisa.destroy();
                        }

                        var temuanBackgroundPlugin = {
                            id: 'temuanBackgroundPlugin',
                            beforeDatasetsDraw: function (chart) {
                                var ctx2 = chart.ctx;
                                ctx2.save();

                                function drawRoundedRect(ctx, x, y, width, height, radius) {
                                    var safeRadius = Math.min(radius, width / 2, height / 2);
                                    ctx.beginPath();
                                    ctx.moveTo(x + safeRadius, y);
                                    ctx.lineTo(x + width - safeRadius, y);
                                    ctx.quadraticCurveTo(x + width, y, x + width, y + safeRadius);
                                    ctx.lineTo(x + width, y + height - safeRadius);
                                    ctx.quadraticCurveTo(x + width, y + height, x + width - safeRadius, y + height);
                                    ctx.lineTo(x + safeRadius, y + height);
                                    ctx.quadraticCurveTo(x, y + height, x, y + height - safeRadius);
                                    ctx.lineTo(x, y + safeRadius);
                                    ctx.quadraticCurveTo(x, y, x + safeRadius, y);
                                    ctx.closePath();
                                }

                                (chart.data.datasets || []).forEach(function (dataset, datasetIndex) {
                                    var meta = chart.getDatasetMeta(datasetIndex);
                                    var temuan = Array.isArray(dataset.temuan) ? dataset.temuan : [];
                                    if (!meta || meta.hidden) {
                                        return;
                                    }

                                    meta.data.forEach(function (element, index) {
                                        var temuanInfo = temuan[index] || null;
                                        if (!element || !temuanInfo || temuanInfo.has_temuan !== true) {
                                            return;
                                        }

                                        var props = typeof element.getProps === 'function'
                                            ? element.getProps(['x', 'y'], true)
                                            : { x: element.x, y: element.y };

                                        drawRoundedRect(ctx2, props.x - 10, props.y - 10, 20, 20, 6);
                                        ctx2.fillStyle = 'rgba(220, 53, 69, 0.22)';
                                        ctx2.fill();
                                    });
                                });

                                ctx2.restore();
                            }
                        };

                        chartAnalisa = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: chartResponse.labels,
                                datasets: chartResponse.datasets || []
                            },
                            plugins: [temuanBackgroundPlugin],
                            options: {
                                responsive: true,
                                plugins: {
                                    tooltip: {
                                        mode: 'index',
                                        intersect: false,
                                        callbacks: {
                                            afterBody: function (items) {
                                                if (!items || items.length === 0) {
                                                    return [];
                                                }

                                                var lines = [];
                                                items.forEach(function (item) {
                                                    var dataset = (chartResponse.datasets || [])[item.datasetIndex] || null;
                                                    var temuan = dataset && Array.isArray(dataset.temuan)
                                                        ? (dataset.temuan[item.dataIndex] || null)
                                                        : null;

                                                    if (!temuan || temuan.has_temuan !== true) {
                                                        return;
                                                    }

                                                    lines.push(dataset.label + ': Temuan ' + temuan.count);
                                                    lines.push('Gol: ' + (temuan.gol_detail || '-'));
                                                });

                                                return lines;
                                            }
                                        }
                                    }
                                },
                                interaction: {
                                    mode: 'index',
                                    intersect: false
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true
                                    }
                                }
                            }
                        });
                    }
                });

                var detailModal = new bootstrap.Modal(document.getElementById('modalDetail'));
                detailModal.show();
            },
            error: function (xhr) {
                var fresh = xhr.getResponseHeader('X-CSRF-TOKEN');
                if (fresh) {
                    csrfToken = fresh;
                }
                Swal.fire('Gagal', 'Detail analisa gagal dimuat.', 'error');
            }
        });
    };

    function reloadAnalisa() {
        table.ajax.reload();
    }

    $('#tahun, #unit, #temuan_status').on('change', function () { reloadAnalisa(); });
    $('#idpel').on('keyup', function () { table.ajax.reload(); });

    $('#tableAnalisa').on('preXhr.dt', function () {
        Swal.fire({ title: 'Mohon Tunggu', html: 'Memuat data analisa', allowOutsideClick: false, showConfirmButton: false, didOpen: function(){ Swal.showLoading(); } });
    });
    $('#tableAnalisa').on('xhr.dt', function () {
        Swal.close();
    });

    $('#formImportAnalisa').on('submit', function () {
        Swal.fire({ title: 'Mohon Tunggu', html: 'Proses import analisa berlangsung', allowOutsideClick: false, showConfirmButton: false, didOpen: function(){ Swal.showLoading(); } });
    });

    $('#btnExportAnalisa').on('click', async function () {
        var qs = $.param({
            tahun: $('#tahun').val(),
            unit: $('#unit').val(),
            idpel: $('#idpel').val() || '',
            temuan_status: $('#temuan_status').val() || '*'
        });
        var url = '<?= site_url('C_P2TL/exportAnalisaExcel') ?>?' + qs;

        Swal.fire({
            title: 'Export Analisa',
            html: '<div id="exportProgressText" class="mb-2">Menyiapkan file... 0%</div>' +
                  '<div class="progress" style="height: 14px;">' +
                  '<div id="exportProgressBar" class="progress-bar" role="progressbar" style="width: 0%">0%</div>' +
                  '</div>',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: function () {
                Swal.showLoading();
            }
        });

        var setProgress = function (percent, text) {
            var p = Math.max(0, Math.min(100, percent));
            var bar = document.getElementById('exportProgressBar');
            var label = document.getElementById('exportProgressText');
            if (bar) {
                bar.style.width = p + '%';
                bar.textContent = p + '%';
            }
            if (label) {
                label.textContent = text || ('Memproses export... ' + p + '%');
            }
        };

        try {
            var response = await fetch(url, {
                method: 'GET',
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error('HTTP ' + response.status);
            }

            var disposition = response.headers.get('Content-Disposition') || '';
            var fileName = 'Analisa_P2TL.xlsx';
            var fileNameMatch = disposition.match(/filename="?([^";]+)"?/i);
            if (fileNameMatch && fileNameMatch[1]) {
                fileName = fileNameMatch[1];
            }

            var contentLength = parseInt(response.headers.get('Content-Length') || '0', 10);
            var receivedLength = 0;

            if (response.body && typeof response.body.getReader === 'function') {
                var reader = response.body.getReader();
                var chunks = [];

                while (true) {
                    var result = await reader.read();
                    if (result.done) {
                        break;
                    }

                    chunks.push(result.value);
                    receivedLength += result.value.length;

                    if (contentLength > 0) {
                        var pct = Math.round((receivedLength / contentLength) * 100);
                        setProgress(pct, 'Mengunduh file export... ' + pct + '%');
                    } else {
                        var pseudo = Math.min(95, Math.round(receivedLength / 10240));
                        setProgress(pseudo, 'Mengunduh file export...');
                    }
                }

                setProgress(100, 'Selesai 100%');
                var blob = new Blob(chunks, { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                var downloadUrl = window.URL.createObjectURL(blob);
                var link = document.createElement('a');
                link.href = downloadUrl;
                link.download = fileName;
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.URL.revokeObjectURL(downloadUrl);
            } else {
                var blobFallback = await response.blob();
                setProgress(100, 'Selesai 100%');
                var downloadUrlFallback = window.URL.createObjectURL(blobFallback);
                var linkFallback = document.createElement('a');
                linkFallback.href = downloadUrlFallback;
                linkFallback.download = fileName;
                document.body.appendChild(linkFallback);
                linkFallback.click();
                linkFallback.remove();
                window.URL.revokeObjectURL(downloadUrlFallback);
            }

            setTimeout(function () {
                Swal.close();
            }, 400);
        } catch (err) {
            Swal.fire('Gagal', 'Export analisa gagal: ' + (err && err.message ? err.message : 'unknown error'), 'error');
        }
    });
})();
</script>
<?= $this->endSection() ?>
