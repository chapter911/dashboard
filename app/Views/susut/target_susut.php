<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php $currentYear = (int) ($currentYear ?? date('Y')); ?>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Data Target Susut</h5>
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <i class="ti ti-upload"></i> Upload Excel
        </button>
    </div>
    <div class="card-body">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success" role="alert"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <form id="targetSusutFilterForm" class="row g-3" method="post" action="<?= site_url('C_Susut/get_target_susut_data') ?>">
            <?= csrf_field() ?>
            <div class="col-md-3">
                <label class="form-label" for="tahun_filter">Pilih Tahun</label>
                <select name="tahun_filter" id="tahun_filter" class="form-select">
                    <?php for ($i = $currentYear - 2; $i <= $currentYear + 2; $i++): ?>
                        <option value="<?= esc((string) $i) ?>" <?= $i === $currentYear ? 'selected' : '' ?>><?= esc((string) $i) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </form>

        <div id="ajaxDataContainer" class="mt-3"></div>
    </div>
</div>

<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload Excel Target Susut</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= site_url('C_Susut/upload_target_susut') ?>" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label" for="tahun">Tahun Target</label>
                        <select name="tahun" id="tahun" class="form-select" required>
                            <?php for ($i = $currentYear - 2; $i <= $currentYear + 2; $i++): ?>
                                <option value="<?= esc((string) $i) ?>" <?= $i === $currentYear ? 'selected' : '' ?>><?= esc((string) $i) ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="excel_file">File Excel (.xlsx, .xls)</label>
                        <input type="file" name="excel_file" id="excel_file" class="form-control" accept=".xlsx,.xls" required>
                    </div>
                    <p class="text-sm mb-0">Belum punya format? <a href="<?= site_url('C_Susut/download_format_target_susut') ?>">Download format</a></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(function () {
        var $form = $('#targetSusutFilterForm');
        var $container = $('#ajaxDataContainer');
        var csrfField = $form.find('input[name="<?= esc(csrf_token()) ?>"]');

        function loadData() {
            $.ajax({
                url: '<?= site_url('C_Susut/get_target_susut_data') ?>',
                type: 'POST',
                data: {
                    tahun: $('#tahun_filter').val(),
                    '<?= esc(csrf_token()) ?>': csrfField.val()
                },
                beforeSend: function () {
                    $container.html('<div class="text-center text-muted py-3">Memuat data target...</div>');
                },
                success: function (response, _textStatus, xhr) {
                    var freshToken = xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null;
                    if (freshToken) {
                        csrfField.val(freshToken);
                    }
                    $container.html(response);
                },
                error: function (xhr) {
                    var freshToken = xhr ? xhr.getResponseHeader('X-CSRF-TOKEN') : null;
                    if (freshToken) {
                        csrfField.val(freshToken);
                    }
                    $container.html('<div class="alert alert-danger mb-0">Gagal mengambil data target susut.</div>');
                }
            });
        }

        $('#tahun_filter').on('change', function () {
            loadData();
        });

        loadData();
    });
</script>
<?= $this->endSection() ?>
