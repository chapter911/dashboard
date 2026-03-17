<?php
$bulan = is_array($bulan ?? null) ? $bulan : [];
$unitGrafik = is_array($unitGrafik ?? null) ? $unitGrafik : [];
$data = is_array($data ?? null) ? $data : [];
$dataUid = is_array($data_uid ?? null) ? $data_uid : [];
$targetSusut = is_array($target_susut ?? null) ? $target_susut : [];
$jenisSusut = (string) ($jenis_susut ?? 'netto');
$currentYear = (int) date('Y');
?>

<link rel="stylesheet" href="<?= base_url('assets/vendor/libs/apex-charts/apex-charts.css') ?>">

<div class="col-12">
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12">
                    <div style="width:100%; height:400px;">
                        <div id="susutChart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-12">
    <div class="card shadow-sm">
        <div class="card-body">
            <h5>Perbandingan Antar Tahun</h5>
            <div class="row mb-3">
                <div class="col-md-3">
                    <label>Unit</label>
                    <select class="form-control" id="comp_unit" onchange="updateComparisonChart()">
                        <option value="">-- Pilih Unit --</option>
                        <?php foreach ($unitGrafik as $u): ?>
                            <?php $unitId = (int) ($u['unit_id'] ?? 0); ?>
                            <?php $unitName = (string) ($u['unit_name'] ?? ''); ?>
                            <option value="<?= esc((string) $unitId) ?>"><?= esc($unitName) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Jenis Perbandingan</label>
                    <select class="form-control" id="comp_type" onchange="updateComparisonChart()">
                        <option value="bulanan">Bulanan</option>
                        <option value="akumulasi">Akumulasi</option>
                        <option value="semua">Semua</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Tahun 1</label>
                    <select class="form-control" id="comp_year1" onchange="updateComparisonChart()">
                        <?php for ($i = $currentYear; $i >= 2020; $i--): ?>
                            <option value="<?= esc((string) $i) ?>" <?= $i === $currentYear ? 'selected' : '' ?>><?= esc((string) $i) ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>Tahun 2</label>
                    <select class="form-control" id="comp_year2" onchange="updateComparisonChart()">
                        <?php for ($i = $currentYear; $i >= 2020; $i--): ?>
                            <option value="<?= esc((string) $i) ?>" <?= $i === ($currentYear - 1) ? 'selected' : '' ?>><?= esc((string) $i) ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <div style="width:100%; height:400px;">
                <div id="comparisonChart"></div>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/vendor/libs/apex-charts/apexcharts.js') ?>"></script>
<script>
    var allData = <?= json_encode($data, JSON_UNESCAPED_UNICODE) ?>;
    var targetData = <?= json_encode($targetSusut, JSON_UNESCAPED_UNICODE) ?>;
    var uidData = <?= json_encode($dataUid, JSON_UNESCAPED_UNICODE) ?>;
    var allMonths = <?= json_encode($bulan, JSON_UNESCAPED_UNICODE) ?>;
    var filteredType = "<?= esc($jenisSusut) ?>";

    var unitSelect = document.getElementById('unit_susut');
    var chartContainer = document.getElementById('susutChart');
    var csrfInput = $('#susutFilterForm input[name="<?= esc(csrf_token()) ?>"]');

    var initialOptions = {
        series: [],
        chart: {
            height: 400,
            type: 'line',
            zoom: { enabled: false },
            toolbar: { show: true }
        },
        plotOptions: {
            bar: {
                columnWidth: '20%'
            }
        },
        noData: {
            text: 'Silakan pilih unit untuk menampilkan grafik.'
        },
        title: {
            text: '',
            align: 'left'
        },
        dataLabels: {
            enabled: true,
            formatter: function (val) {
                if (val === 0 || val === null) {
                    return '';
                }
                return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(val);
            },
            offsetY: -5,
            style: { fontWeight: 'bold' },
            background: {
                enabled: true,
                foreColor: '#fff',
                padding: 4,
                borderRadius: 2,
                borderWidth: 1,
                borderColor: '#ddd',
                opacity: 0.9
            }
        },
        stroke: { curve: 'straight', width: 2 },
        colors: ['#4CAF50', '#F44336'],
        grid: {
            show: true
        },
        xaxis: {
            categories: allMonths.map(function (m) { return m.singkatan; }),
            title: { text: 'Bulan' }
        },
        yaxis: {
            title: { text: '(%)' },
            max: 10,
            min: 0,
            labels: {
                formatter: function (value) {
                    return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Math.round(value));
                }
            }
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Math.round(val));
                }
            }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'right',
            floating: true,
            offsetY: -25,
            offsetX: -5
        }
    };

    var susutChart = new ApexCharts(chartContainer, initialOptions);
    susutChart.render();

    function updateChart() {
        if (!unitSelect || unitSelect.selectedIndex < 0) {
            return;
        }

        var selectedUnitId = unitSelect.value;
        var selectedUnitName = unitSelect.options[unitSelect.selectedIndex].text || '';
        var selectedType = filteredType;

        if (!selectedUnitId) {
            susutChart.updateSeries([]);
            susutChart.updateOptions({ title: { text: '' } });
            return;
        }

        var monthlyData = [];
        var cumulativeData = [];
        var targetSeriesData = [];
        var isUID = selectedUnitName.toUpperCase().indexOf('UID') !== -1;

        for (var i = 0; i < allMonths.length; i++) {
            var month = allMonths[i];
            var monthNumber = parseInt(month.bulan, 10);

            var targetPoint = targetData.find(function (t) {
                return String(t.unit_id) === String(selectedUnitId) && parseInt(t.bulan, 10) === monthNumber;
            });
            targetSeriesData.push(targetPoint ? parseFloat(targetPoint.nilai) : 0);

            if (isUID) {
                var uidPoint = uidData.find(function (d) {
                    var dataMonth = new Date(d.periode).getMonth() + 1;
                    return dataMonth === monthNumber;
                });

                monthlyData.push(uidPoint ? parseFloat(uidPoint.persentase) : 0);
                cumulativeData.push(uidPoint ? parseFloat(uidPoint.akumulasi_persentase) : 0);
            } else {
                var dataPoint = allData.find(function (d) {
                    var dataMonth = new Date(d.periode).getMonth() + 1;
                    return String(d.unit_id) === String(selectedUnitId) && dataMonth === monthNumber;
                });

                if (selectedType === 'netto') {
                    monthlyData.push(dataPoint ? parseFloat(dataPoint.netto_tt) : 0);
                    cumulativeData.push(dataPoint ? parseFloat(dataPoint.netto_cumulative_tt) : 0);
                } else {
                    monthlyData.push(dataPoint ? parseFloat(dataPoint.bruto_tt) : 0);
                    cumulativeData.push(dataPoint ? parseFloat(dataPoint.bruto_cumulative_tt) : 0);
                }
            }
        }

        susutChart.updateSeries([
            {
                name: 'Bulanan',
                data: monthlyData
            },
            {
                name: 'Akumulasi',
                data: cumulativeData
            },
            {
                name: 'Target',
                data: targetSeriesData
            }
        ]);

        var typeLabel = selectedType === 'netto' ? 'Netto' : 'Bruto';
        var chartColors = selectedType === 'netto'
            ? ['#4CAF50', '#F44336', '#FF9800']
            : ['#008FFB', '#FF4560', '#FF9800'];

        var allValues = monthlyData.concat(cumulativeData, targetSeriesData);
        var maxVal = Math.max.apply(null, allValues.concat([0]));
        var minVal = Math.min.apply(null, allValues.concat([0]));
        var yAxisMax = maxVal > 10 ? Math.ceil(maxVal) : 10;
        var yAxisMin = minVal < 0 ? Math.floor(minVal) : 0;

        susutChart.updateOptions({
            title: {
                text: typeLabel,
                align: 'center'
            },
            stroke: {
                curve: 'straight',
                width: [2, 2, 2],
                dashArray: [0, 0, 6]
            },
            yaxis: {
                title: { text: '(%)' },
                min: yAxisMin,
                max: yAxisMax,
                forceNiceScale: true,
                labels: {
                    formatter: function (value) {
                        return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(Math.round(value));
                    }
                }
            },
            colors: chartColors
        });
    }

    if (unitSelect) {
        unitSelect.onchange = updateChart;
        if (unitSelect.value) {
            updateChart();
        }
    }

    var comparisonChart = null;

    function updateComparisonChart() {
        var year1 = document.getElementById('comp_year1').value;
        var year2 = document.getElementById('comp_year2').value;
        var unitCompSelect = document.getElementById('comp_unit');

        var selectedUnitId = unitCompSelect.value;
        var selectedUnitName = unitCompSelect.options[unitCompSelect.selectedIndex].text || '';
        var selectedType = filteredType;
        var viewType = document.getElementById('comp_type').value;

        if (!selectedUnitId) {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Perhatian', 'Silakan pilih unit terlebih dahulu.', 'warning');
            }
            return;
        }

        var isUID = selectedUnitName.toUpperCase().indexOf('UID') !== -1;

        $.ajax({
            url: "<?= base_url('C_Susut/getComparisonData'); ?>",
            type: 'POST',
            data: {
                year1: year1,
                year2: year2,
                unit_id: selectedUnitId,
                jenis_susut: selectedType,
                is_uid: isUID,
                '<?= esc(csrf_token()) ?>': csrfInput.val()
            },
            beforeSend: function () {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Mohon Tunggu',
                        html: 'Mengambil Data Perbandingan',
                        allowOutsideClick: false,
                        showCancelButton: false,
                        showConfirmButton: false,
                        didOpen: function () {
                            Swal.showLoading();
                        }
                    });
                }
            },
            success: function (response, _textStatus, xhr) {
                var freshToken = xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null;
                if (freshToken) {
                    csrfInput.val(freshToken);
                }

                if (typeof Swal !== 'undefined') {
                    Swal.close();
                }

                if (response.status === 'success') {
                    renderComparisonChart(response.data1, response.data2, year1, year2, selectedType, isUID, viewType);
                } else if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', 'Gagal memuat data.', 'error');
                }
            },
            error: function (xhr) {
                var freshToken = xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null;
                if (freshToken) {
                    csrfInput.val(freshToken);
                }

                if (typeof Swal !== 'undefined') {
                    Swal.close();
                    Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                }
            }
        });
    }

    function renderComparisonChart(data1, data2, year1, year2, type, isUID, viewType) {
        var months = allMonths.map(function (m) { return m.singkatan; });

        function processData(rawData) {
            var monthly = [];
            var cumulative = [];

            allMonths.forEach(function (m) {
                var monthNum = parseInt(m.bulan, 10);
                var point;

                if (isUID) {
                    point = rawData.find(function (d) {
                        var dMonth = new Date(d.periode).getMonth() + 1;
                        return dMonth === monthNum;
                    });
                    monthly.push(point ? parseFloat(point.persentase) : 0);
                    cumulative.push(point ? parseFloat(point.akumulasi_persentase) : 0);
                } else {
                    point = rawData.find(function (d) {
                        var dMonth = new Date(d.periode).getMonth() + 1;
                        return dMonth === monthNum;
                    });

                    if (type === 'netto') {
                        monthly.push(point ? parseFloat(point.netto_tt) : 0);
                        cumulative.push(point ? parseFloat(point.netto_cumulative_tt) : 0);
                    } else {
                        monthly.push(point ? parseFloat(point.bruto_tt) : 0);
                        cumulative.push(point ? parseFloat(point.bruto_cumulative_tt) : 0);
                    }
                }
            });

            return { monthly: monthly, cumulative: cumulative };
        }

        var d1 = processData(data1 || []);
        var d2 = processData(data2 || []);

        var series = [];
        var colors = [];
        var dashArray = [];
        var strokeWidth = [];

        if (viewType === 'bulanan' || viewType === 'semua') {
            series.push({ name: 'Bulanan ' + year1, type: 'line', data: d1.monthly });
            colors.push('#4CAF50');
            dashArray.push(0);
            strokeWidth.push(2);

            series.push({ name: 'Bulanan ' + year2, type: 'line', data: d2.monthly });
            colors.push('#F44336');
            dashArray.push(0);
            strokeWidth.push(2);
        }

        if (viewType === 'akumulasi' || viewType === 'semua') {
            series.push({ name: 'Akumulasi ' + year1, type: 'line', data: d1.cumulative });
            colors.push('#2E7D32');
            dashArray.push(0);
            strokeWidth.push(2);

            series.push({ name: 'Akumulasi ' + year2, type: 'line', data: d2.cumulative });
            colors.push('#C62828');
            dashArray.push(0);
            strokeWidth.push(2);
        }

        var options = {
            series: series,
            chart: {
                height: 400,
                type: 'line',
                zoom: { enabled: false },
                toolbar: { show: true }
            },
            stroke: { width: strokeWidth, curve: 'straight', dashArray: dashArray },
            colors: colors,
            dataLabels: { enabled: false },
            xaxis: {
                categories: months
            },
            yaxis: {
                title: { text: '(%)' },
                labels: {
                    formatter: function (value) {
                        return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(value);
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 }).format(val);
                    }
                }
            },
            title: {
                text: 'Perbandingan ' + type.toUpperCase() + ' ' + year1 + ' vs ' + year2,
                align: 'center'
            }
        };

        if (comparisonChart) {
            comparisonChart.destroy();
        }
        comparisonChart = new ApexCharts(document.querySelector('#comparisonChart'), options);
        comparisonChart.render();
    }
</script>
