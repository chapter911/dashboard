<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$months = is_array($months ?? null) ? $months : [];
$units = is_array($units ?? null) ? $units : [];
$currentYear = (int) ($currentYear ?? date('Y'));
?>

<link rel="stylesheet" href="<?= base_url('assets/vendor/libs/select2/select2.css') ?>">

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Filter Data Susut</h5>
    </div>
    <div class="card-body">
        <form id="susutFilterForm" class="row g-3" method="post" action="<?= site_url('C_Susut/getDataSusut') ?>">
            <?= csrf_field() ?>
            <div class="col-md-3">
                <label class="form-label">Periode Tahun</label>
                <select class="form-select" id="tahun" name="tahun">
                    <?php for ($year = $currentYear + 1; $year >= 2020; $year--): ?>
                        <option value="<?= esc((string) $year) ?>" <?= $year === $currentYear ? 'selected' : '' ?>><?= esc((string) $year) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Jenis Susut</label>
                <select class="form-select" id="jenis_susut" name="jenis_susut">
                    <option value="netto" selected>Susut Netto</option>
                    <option value="bruto">Susut Bruto</option>
                </select>
            </div>
            <div class="col-md-3" id="col-unit" style="display:none;">
                <label class="form-label">Unit Grafik</label>
                <select class="form-select select2" id="unit_susut" name="unit_susut" data-placeholder="Pilih Unit">
                    <option value="">Pilih Unit</option>
                    <?php foreach ($units as $unit): ?>
                        <?php $unitId = (int) ($unit['unit_id'] ?? 0); ?>
                        <?php $unitName = (string) ($unit['unit_name'] ?? ''); ?>
                        <option value="<?= esc((string) $unitId) ?>">
                            <?= esc($unitName) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end justify-content-md-end">
                <div class="btn-group w-100" role="group" aria-label="Tampilan Susut">
                    <button type="button" id="btnViewTabel" class="btn btn-primary">Tabel</button>
                    <button type="button" id="btnViewGrafik" class="btn btn-outline-primary">Grafik</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="ajaxDataContainer"></div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/vendor/libs/select2/select2.js') ?>"></script>
<script>
    $(function () {
        var $form = $('#susutFilterForm');
        var $container = $('#ajaxDataContainer');
        var $unitCol = $('#col-unit');
        var currentView = 'tabel';
        var csrfFieldName = '<?= esc(csrf_token()) ?>';

        $('.select2').select2({ width: '100%' });

        function setView(view) {
            currentView = view;
            var isGrafik = view === 'grafik';

            $('#btnViewTabel')
                .toggleClass('btn-primary', !isGrafik)
                .toggleClass('btn-outline-primary', isGrafik);

            $('#btnViewGrafik')
                .toggleClass('btn-primary', isGrafik)
                .toggleClass('btn-outline-primary', !isGrafik);

            $unitCol.toggle(isGrafik);

            loadData();
        }

        function applyCsrf(freshToken) {
            if (!freshToken) {
                return;
            }
            $form.find('input[name="' + csrfFieldName + '"]').val(freshToken);
        }

        function loadData() {
            var payload = $form.serializeArray();
            payload.push({ name: 'tampilan', value: currentView });

            $.ajax({
                url: '<?= site_url('C_Susut/getDataSusut') ?>',
                type: 'POST',
                data: payload,
                beforeSend: function () {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Mohon Tunggu',
                            html: 'Mengambil data susut...',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            didOpen: function () {
                                Swal.showLoading();
                            }
                        });
                    }
                },
                success: function (response, _textStatus, xhr) {
                    applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
                    $container.html(response);
                },
                error: function (xhr) {
                    applyCsrf(xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Gagal', 'Data susut gagal dimuat.', 'error');
                    }
                },
                complete: function () {
                    if (typeof Swal !== 'undefined') {
                        Swal.close();
                    }
                }
            });
        }

        $('#btnViewTabel').on('click', function () {
            setView('tabel');
        });

        $('#btnViewGrafik').on('click', function () {
            setView('grafik');
        });

        $('#tahun, #jenis_susut').on('change', function () {
            loadData();
        });

        setView('tabel');
    });
</script>
<?= $this->endSection() ?>
