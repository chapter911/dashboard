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
.table-detail-temuan td.temuan-value-highlight {
    background-color: rgba(220, 53, 69, 0.18) !important;
}
.form-select:focus,
.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}
body.modal-open {
    padding-right: 0 !important;
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
                    <option value="P1">Temuan P1</option>
                    <option value="P2">Temuan P2</option>
                    <option value="P3">Temuan P3</option>
                    <option value="P4">Temuan P4</option>
                    <option value="K2">Temuan K2</option>
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
                <div class="mb-3 d-flex flex-wrap justify-content-between align-items-start gap-2" id="detailMeta">
                    <div>
                        <strong>Tarif:</strong> <span id="detailTarif">-</span>
                        &nbsp; | &nbsp;
                        <strong>Daya:</strong> <span id="detailDaya">-</span>
                    </div>
                    <div class="d-flex align-items-center gap-2" id="detailTemuanControls">
                        <select class="form-select form-select-sm" id="selectRentangAnalisa" style="width: 130px;">
                            <option value="3">Triwulan</option>
                            <option value="6">Semester</option>
                            <option value="12" selected>Tahunan</option>
                        </select>
                        <select class="form-select form-select-sm d-none" id="selectTemuanAnalisa" style="min-width: 220px; max-width: 320px;">
                            <option value="">Semua Temuan</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <canvas id="chartAnalisa" height="120"></canvas>
                    <div class="small text-muted mt-2">Keterangan: titik merah temuan dapat diklik untuk menampilkan urutan 12 bulan.</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-detail-temuan">
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
    var detailModalEl = document.getElementById('modalDetail');
    var detailModal = detailModalEl ? bootstrap.Modal.getOrCreateInstance(detailModalEl) : null;

    if (detailModalEl) {
        detailModalEl.addEventListener('shown.bs.modal', function () {
            document.body.style.paddingRight = '';
        });
        detailModalEl.addEventListener('hidden.bs.modal', function () {
            document.body.style.paddingRight = '';
        });
    }

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
                var temuanSelect = $('#selectTemuanAnalisa');
                var rentangSelect = $('#selectRentangAnalisa');
                temuanSelect.addClass('d-none').html('<option value="">Semua Temuan</option>').val('');

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

                    var detailRows = Array.isArray(response.rows) ? response.rows : [];
                    // Continuous 36-month series: [Jan(y-2)..Dec(y-2), Jan(y-1)..Dec(y-1), Jan(y)..Dec(y)]
                    var continuousSeries = Array.isArray(response.jn_continuous)
                        ? response.jn_continuous.map(function (v) { return v === null ? null : Number(v); })
                        : [];
                    while (continuousSeries.length < 36) { continuousSeries.push(null); }

                    function buildDetailRowHtml(row, rowIndex, labelText, baseIdxOverride, isActive, temuanYears) {
                        var rowHtml = '<tr><td>' + labelText + '</td>';
                        var highlightedYears = temuanYears && typeof temuanYears === 'object' ? temuanYears : {};

                        $.each(years, function (_idx, year) {
                            var kwhData = row.pemakaian_kwh || {};
                            var value = isActive
                                ? (Object.prototype.hasOwnProperty.call(kwhData, String(year)) ? kwhData[String(year)] : null)
                                : null;
                            var kwhClass = highlightedYears[String(year)] === true ? 'text-end temuan-value-highlight' : 'text-end';
                            rowHtml += '<td class="' + kwhClass + '">' + (value === null ? '-' : Number(value).toLocaleString('id-ID', { maximumFractionDigits: 0 })) + '</td>';
                        });

                        $.each(years, function (_idx, year) {
                            var nyalaData = row.jam_nyala || {};
                            var value = isActive
                                ? (Object.prototype.hasOwnProperty.call(nyalaData, String(year)) ? nyalaData[String(year)] : null)
                                : null;
                            var nyalaClass = highlightedYears[String(year)] === true ? 'text-end temuan-value-highlight' : 'text-end';
                            rowHtml += '<td class="' + nyalaClass + '">' + (value === null ? '-' : Number(value).toLocaleString('id-ID', { maximumFractionDigits: 0 })) + '</td>';
                        });

                        var bulanan = null;
                        var triwulan = null;
                        var semester = null;
                        var tahunan = null;

                        if (isActive) {
                            var baseIdx = typeof baseIdxOverride === 'number' ? baseIdxOverride : (24 + rowIndex);
                            bulanan = percentDiff(continuousSeries[baseIdx], continuousSeries[baseIdx - 1]);
                            triwulan = percentDiff(
                                continuousWindowAvg(continuousSeries, baseIdx - 2, baseIdx),
                                continuousWindowAvg(continuousSeries, baseIdx - 5, baseIdx - 3)
                            );
                            semester = percentDiff(
                                continuousWindowAvg(continuousSeries, baseIdx - 5, baseIdx),
                                continuousWindowAvg(continuousSeries, baseIdx - 11, baseIdx - 6)
                            );
                            tahunan = percentDiff(
                                continuousWindowAvg(continuousSeries, baseIdx - 11, baseIdx),
                                continuousWindowAvg(continuousSeries, baseIdx - 23, baseIdx - 12)
                            );
                        }

                        rowHtml += '<td class="text-end">' + formatPercent(bulanan) + '</td>';
                        rowHtml += '<td class="text-end">' + formatPercent(triwulan) + '</td>';
                        rowHtml += '<td class="text-end">' + formatPercent(semester) + '</td>';
                        rowHtml += '<td class="text-end">' + formatPercent(tahunan) + '</td>';
                        rowHtml += '</tr>';

                        return rowHtml;
                    }

                    function renderDetailRows(orderItems) {
                        var bodyHtml = '';
                        orderItems.forEach(function (item) {
                            var row = detailRows[item.rowIndex] || null;
                            if (!row) {
                                return;
                            }
                            bodyHtml += buildDetailRowHtml(
                                row,
                                item.rowIndex,
                                item.label || row.bulan,
                                item.baseIdx,
                                item.active !== false,
                                item.temuanYears || {}
                            );
                        });
                        $('#detailBody').html(bodyHtml);
                    }

                    var defaultDetailOrder = detailRows.map(function (row, idx) {
                        return {
                            rowIndex: idx,
                            label: row.bulan,
                            baseIdx: 24 + idx,
                            active: true,
                            temuanYears: {},
                        };
                    });

                    renderDetailRows(defaultDetailOrder);
                }

                if (!response.has_data) {
                    $('#detailBody').html(html);
                }

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
                                var chartArea = chart.chartArea;
                                var xScale = chart.scales && chart.scales.x;

                                if (!chartArea || !xScale) {
                                    return;
                                }

                                ctx2.save();

                                var monthCount = Array.isArray(chart.data.labels) ? chart.data.labels.length : 0;
                                var highlightedMonths = new Array(monthCount).fill(false);

                                (chart.data.datasets || []).forEach(function (dataset) {
                                    var temuan = Array.isArray(dataset.temuan) ? dataset.temuan : [];
                                    temuan.forEach(function (temuanInfo, index) {
                                        if (temuanInfo && temuanInfo.has_temuan === true) {
                                            highlightedMonths[index] = true;
                                        }
                                    });
                                });

                                highlightedMonths.forEach(function (isHighlighted, index) {
                                    if (!isHighlighted) {
                                        return;
                                    }

                                    var centerX = xScale.getPixelForValue(index);
                                    var prevX = index > 0 ? xScale.getPixelForValue(index - 1) : null;
                                    var nextX = index < monthCount - 1 ? xScale.getPixelForValue(index + 1) : null;
                                    var bandWidth;

                                    if (prevX !== null && nextX !== null) {
                                        bandWidth = Math.abs(nextX - prevX) / 2;
                                    } else if (nextX !== null) {
                                        bandWidth = Math.abs(nextX - centerX);
                                    } else if (prevX !== null) {
                                        bandWidth = Math.abs(centerX - prevX);
                                    } else {
                                        bandWidth = chartArea.right - chartArea.left;
                                    }

                                    var left = Math.max(chartArea.left, centerX - (bandWidth / 2));
                                    var right = Math.min(chartArea.right, centerX + (bandWidth / 2));

                                    ctx2.fillStyle = 'rgba(220, 53, 69, 0.18)';
                                    ctx2.fillRect(left, chartArea.top, Math.max(0, right - left), chartArea.bottom - chartArea.top);
                                });

                                ctx2.restore();
                            }
                        };

                        function cloneDataset(dataset) {
                            return {
                                label: dataset.label,
                                borderColor: dataset.borderColor,
                                backgroundColor: dataset.backgroundColor,
                                tension: dataset.tension,
                                fill: dataset.fill,
                                data: Array.isArray(dataset.data) ? dataset.data.slice() : [],
                                temuan: Array.isArray(dataset.temuan) ? dataset.temuan.slice() : [],
                                pointBackgroundColor: Array.isArray(dataset.pointBackgroundColor) ? dataset.pointBackgroundColor.slice() : dataset.pointBackgroundColor,
                                pointBorderColor: Array.isArray(dataset.pointBorderColor) ? dataset.pointBorderColor.slice() : dataset.pointBorderColor,
                                pointRadius: Array.isArray(dataset.pointRadius) ? dataset.pointRadius.slice() : dataset.pointRadius,
                                pointHoverRadius: Array.isArray(dataset.pointHoverRadius) ? dataset.pointHoverRadius.slice() : dataset.pointHoverRadius
                            };
                        }

                        var originalChartPayload = {
                            labels: Array.isArray(chartResponse.labels) ? chartResponse.labels.slice() : [],
                            datasets: (chartResponse.datasets || []).map(cloneDataset)
                        };
                        var isCountdownMode = false;
                        var isTemuanSelectSyncing = false;
                        var temuanOptionMap = {};
                        var seriesByYear = {};

                        function getSelectedWindowSize() {
                            var value = Number(rentangSelect.val() || 12);
                            if (value !== 3 && value !== 6 && value !== 12) {
                                return 12;
                            }
                            return value;
                        }

                        function parseYearFromLabel(label, fallbackYear) {
                            var match = String(label || '').match(/\((\d{4})\)/);
                            if (match && match[1]) {
                                return Number(match[1]);
                            }
                            return fallbackYear;
                        }

                        function shiftMonth(year, monthIndex, delta) {
                            var d = new Date(year, monthIndex, 1);
                            d.setMonth(d.getMonth() + delta);
                            return {
                                year: d.getFullYear(),
                                monthIndex: d.getMonth(),
                            };
                        }

                        function buildCountdownDetailOrder(startYear, startMonthIndex, sourceMonthLabels, windowSize) {
                            var monthMap = {};
                            var tableSpan = windowSize === 12 ? 12 : (windowSize * 2);

                            for (var offset = tableSpan - 1; offset >= 0; offset--) {
                                var ym = shiftMonth(startYear, startMonthIndex, -offset);
                                var monthIndex = ym.monthIndex;
                                var monthName = sourceMonthLabels[monthIndex] || new Date(ym.year, ym.monthIndex, 1).toLocaleString('id-ID', { month: 'long' });
                                var baseIdx = ((ym.year - (<?= (int) $selectedYear ?> - 2)) * 12) + monthIndex;
                                var periodPoint = getSeriesPointByYearMonth(ym.year, ym.monthIndex);
                                var temuanYears = {};
                                if (periodPoint.temuan && periodPoint.temuan.has_temuan === true) {
                                    temuanYears[String(ym.year)] = true;
                                }
                                monthMap[monthIndex] = {
                                    rowIndex: monthIndex,
                                    label: monthName,
                                    baseIdx: baseIdx,
                                    year: ym.year,
                                    active: true,
                                    temuanYears: temuanYears,
                                };
                            }

                            var order = [];
                            for (var m = 0; m < 12; m++) {
                                if (monthMap[m]) {
                                    order.push(monthMap[m]);
                                } else {
                                    order.push({
                                        rowIndex: m,
                                        label: sourceMonthLabels[m],
                                        baseIdx: 24 + m,
                                        active: true,
                                        temuanYears: {},
                                    });
                                }
                            }

                            // For yearly view, keep month labels in calendar order Jan..Dec.
                            if (windowSize === 12) {
                                order.sort(function (a, b) {
                                    return a.rowIndex - b.rowIndex;
                                });
                            }

                            return order;
                        }

                        function buildSeriesByYear(sourceDatasets) {
                            var map = {};
                            sourceDatasets.forEach(function (dataset) {
                                var year = parseYearFromLabel(dataset.label, Number($('#tahun').val() || new Date().getFullYear()));
                                map[year] = {
                                    data: Array.isArray(dataset.data) ? dataset.data.slice() : [],
                                    temuan: Array.isArray(dataset.temuan) ? dataset.temuan.slice() : [],
                                };
                            });
                            return map;
                        }

                        function refreshDefaultTemuanMarkers() {
                            defaultDetailOrder.forEach(function (item) {
                                var mapByYear = {};

                                (originalChartPayload.datasets || []).forEach(function (dataset) {
                                    var year = String(parseYearFromLabel(dataset.label, Number($('#tahun').val() || new Date().getFullYear())));
                                    var series = Array.isArray(dataset.temuan) ? dataset.temuan : [];
                                    var info = series[item.rowIndex] || null;
                                    mapByYear[year] = !!(info && info.has_temuan === true);
                                });

                                item.temuanYears = mapByYear;
                            });
                        }

                        function getSeriesPointByYearMonth(year, monthIndex) {
                            var yearData = seriesByYear[year];
                            if (!yearData) {
                                return {
                                    value: null,
                                    temuan: {
                                        count: 0,
                                        has_temuan: false,
                                        gol_counts: {},
                                        gol_detail: '-',
                                    },
                                };
                            }

                            return {
                                value: (yearData.data && yearData.data.length > monthIndex) ? yearData.data[monthIndex] : null,
                                temuan: (yearData.temuan && yearData.temuan.length > monthIndex)
                                    ? yearData.temuan[monthIndex]
                                    : {
                                        count: 0,
                                        has_temuan: false,
                                        gol_counts: {},
                                        gol_detail: '-',
                                    },
                            };
                        }

                        function buildTemuanOptions(sourceDatasets, sourceMonthLabels) {
                            var options = [];
                            sourceDatasets.forEach(function (dataset, datasetIndex) {
                                var datasetYear = parseYearFromLabel(dataset.label, Number($('#tahun').val() || new Date().getFullYear()));
                                var temuanSeries = Array.isArray(dataset.temuan) ? dataset.temuan : [];

                                temuanSeries.forEach(function (temuanInfo, monthIndex) {
                                    if (!temuanInfo || temuanInfo.has_temuan !== true) {
                                        return;
                                    }

                                    var golCounts = temuanInfo.gol_counts && typeof temuanInfo.gol_counts === 'object'
                                        ? temuanInfo.gol_counts
                                        : {};
                                    Object.keys(golCounts).forEach(function (golKey) {
                                        var count = Number(golCounts[golKey] || 0);
                                        if (!Number.isFinite(count) || count <= 0) {
                                            return;
                                        }

                                        var value = [datasetIndex, monthIndex, golKey].join('|');
                                        options.push({
                                            value: value,
                                            label: golKey + ' - ' + sourceMonthLabels[monthIndex] + ' ' + datasetYear,
                                            datasetIndex: datasetIndex,
                                            monthIndex: monthIndex
                                        });
                                    });
                                });
                            });

                            return options;
                        }

                        function setTemuanSelectionByPoint(datasetIndex, monthIndex) {
                            var matchedValue = '';
                            Object.keys(temuanOptionMap).some(function (key) {
                                var opt = temuanOptionMap[key];
                                if (opt.datasetIndex === datasetIndex && opt.monthIndex === monthIndex) {
                                    matchedValue = key;
                                    return true;
                                }
                                return false;
                            });

                            isTemuanSelectSyncing = true;
                            temuanSelect.val(matchedValue);
                            isTemuanSelectSyncing = false;
                        }

                        function populateTemuanSelect() {
                            temuanOptionMap = {};
                            var options = buildTemuanOptions(originalChartPayload.datasets, originalChartPayload.labels);

                            if (options.length === 0) {
                                temuanSelect.addClass('d-none').html('<option value="">Semua Temuan</option>').val('');
                                return;
                            }

                            var htmlOptions = '<option value="">Semua Temuan</option>';
                            options.forEach(function (opt) {
                                temuanOptionMap[opt.value] = opt;
                                htmlOptions += '<option value="' + opt.value + '">' + opt.label + '</option>';
                            });

                            temuanSelect.removeClass('d-none').html(htmlOptions).val('');
                        }

                        function resetCountdownView() {
                            if (!chartAnalisa) {
                                return;
                            }

                            chartAnalisa.data.labels = originalChartPayload.labels.slice();
                            chartAnalisa.data.datasets = originalChartPayload.datasets.map(cloneDataset);
                            chartAnalisa.update();

                            if (response.has_data && typeof renderDetailRows === 'function' && typeof defaultDetailOrder !== 'undefined') {
                                renderDetailRows(defaultDetailOrder);
                            }

                            isCountdownMode = false;
                            isTemuanSelectSyncing = true;
                            temuanSelect.val('');
                            isTemuanSelectSyncing = false;
                        }

                        function applyCountdownView(clickedDatasetIndex, clickedDataIndex, forceApply) {
                            if (isCountdownMode && forceApply !== true) {
                                return;
                            }

                            var sourceLabels = originalChartPayload.labels;
                            var sourceDatasets = originalChartPayload.datasets;
                            var clickedSourceDataset = sourceDatasets[clickedDatasetIndex];
                            if (!clickedSourceDataset) {
                                return;
                            }

                            var fallbackYear = Number($('#tahun').val() || new Date().getFullYear());
                            var clickedYear = parseYearFromLabel(clickedSourceDataset.label, fallbackYear);
                            var windowSize = getSelectedWindowSize();
                            var periodOffsets = [0, 1, 2];
                            var newLabels = [];

                            chartAnalisa.data.datasets = sourceDatasets.map(function (dataset, datasetIndex) {
                                var cloned = cloneDataset(dataset);
                                var data = [];
                                var temuan = [];
                                var pointBackgroundColor = [];
                                var pointBorderColor = [];
                                var pointRadius = [];
                                var offsetShift = periodOffsets[datasetIndex] * windowSize;

                                for (var i = 0; i < windowSize; i++) {
                                    var stepFromClicked = (windowSize - 1 - i) + offsetShift;
                                    var ym = shiftMonth(clickedYear, clickedDataIndex, -stepFromClicked);
                                    var point = getSeriesPointByYearMonth(ym.year, ym.monthIndex);

                                    if (datasetIndex === 0) {
                                        var monthName = sourceLabels[ym.monthIndex] || new Date(ym.year, ym.monthIndex, 1).toLocaleString('id-ID', { month: 'long' });
                                        newLabels.push(monthName + ' ' + ym.year);
                                    }

                                    data.push(point.value !== undefined ? point.value : null);
                                    temuan.push(point.temuan);
                                    var hasTemuan = !!(point.temuan && point.temuan.has_temuan === true);
                                    pointBackgroundColor.push(hasTemuan ? '#dc3545' : dataset.borderColor);
                                    pointBorderColor.push(hasTemuan ? '#dc3545' : '#ffffff');
                                    pointRadius.push(hasTemuan ? 5 : 3);
                                }

                                cloned.data = data;
                                cloned.temuan = temuan;
                                cloned.pointBackgroundColor = pointBackgroundColor;
                                cloned.pointBorderColor = pointBorderColor;
                                cloned.pointRadius = pointRadius;
                                cloned.pointHoverRadius = 7;

                                return cloned;
                            });

                            chartAnalisa.data.labels = newLabels;

                            chartAnalisa.update();

                            if (response.has_data && typeof renderDetailRows === 'function') {
                                renderDetailRows(buildCountdownDetailOrder(clickedYear, clickedDataIndex, sourceLabels, windowSize));
                            }

                            isCountdownMode = true;
                            setTemuanSelectionByPoint(clickedDatasetIndex, clickedDataIndex);
                        }

                        temuanSelect.off('change').on('change', function () {
                            if (isTemuanSelectSyncing) {
                                return;
                            }

                            var selectedValue = String($(this).val() || '');
                            if (selectedValue === '') {
                                resetCountdownView();
                                return;
                            }

                            var selected = temuanOptionMap[selectedValue] || null;
                            if (!selected) {
                                return;
                            }

                            applyCountdownView(selected.datasetIndex, selected.monthIndex, true);
                        });

                        rentangSelect.off('change').on('change', function () {
                            var selectedValue = String(temuanSelect.val() || '');
                            var selected = selectedValue !== '' ? (temuanOptionMap[selectedValue] || null) : null;

                            if (selected) {
                                applyCountdownView(selected.datasetIndex, selected.monthIndex, true);
                            } else if (isCountdownMode) {
                                resetCountdownView();
                            }
                        });

                        seriesByYear = buildSeriesByYear(originalChartPayload.datasets);
                        refreshDefaultTemuanMarkers();
                        if (!isCountdownMode && response.has_data && typeof renderDetailRows === 'function') {
                            renderDetailRows(defaultDetailOrder);
                        }
                        populateTemuanSelect();

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
                                                    var dataset = (chartAnalisa && chartAnalisa.data && chartAnalisa.data.datasets
                                                        ? chartAnalisa.data.datasets
                                                        : [])[item.datasetIndex] || null;
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
                                },
                                onClick: function (_event, elements) {
                                    if (!elements || elements.length === 0) {
                                        return;
                                    }

                                    var first = elements[0];
                                    var dataset = chartAnalisa.data.datasets[first.datasetIndex] || null;
                                    var temuan = dataset && Array.isArray(dataset.temuan)
                                        ? (dataset.temuan[first.index] || null)
                                        : null;

                                    if (!temuan || temuan.has_temuan !== true) {
                                        return;
                                    }

                                    applyCountdownView(first.datasetIndex, first.index, false);
                                },
                                onHover: function (event, elements) {
                                    var target = event && event.native ? event.native.target : null;
                                    if (!target) {
                                        return;
                                    }

                                    if (isCountdownMode) {
                                        target.style.cursor = 'default';
                                        return;
                                    }

                                    if (!elements || elements.length === 0) {
                                        target.style.cursor = 'default';
                                        return;
                                    }

                                    var first = elements[0];
                                    var dataset = chartAnalisa.data.datasets[first.datasetIndex] || null;
                                    var temuan = dataset && Array.isArray(dataset.temuan)
                                        ? (dataset.temuan[first.index] || null)
                                        : null;

                                    target.style.cursor = (temuan && temuan.has_temuan === true) ? 'pointer' : 'default';
                                }
                            }
                        });
                    }
                });

                if (detailModal) {
                    detailModal.show();
                }
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
