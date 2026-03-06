<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$histories = is_array($histories ?? null) ? $histories : [];
?>

<div class="row">
    <div class="col-12">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success" role="alert"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Riwayat Login</h5>
                <small class="text-muted">Menampilkan seluruh catatan login/logout pengguna aplikasi.</small>
            </div>
            <div class="card-body pt-2 pb-3 px-3 px-md-4">
                <div class="table-responsive text-nowrap">
                    <table id="loginHistoryTable" class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Username</th>
                            <th>Event</th>
                            <th>Status</th>
                            <th>IP Address</th>
                            <th>IP Network</th>
                            <th>User Agent</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($histories as $index => $row): ?>
                            <?php
                            $createdDateRaw = trim((string) ($row['created_date'] ?? ''));
                            $createdTimestamp = $createdDateRaw !== '' ? strtotime($createdDateRaw) : false;
                            $createdDateDisplay = $createdTimestamp ? date('d-m-Y H:i:s', $createdTimestamp) : '-';
                            $createdOrder = $createdTimestamp ? date('YmdHis', $createdTimestamp) : '0';

                            $eventType = strtoupper(trim((string) ($row['event_type'] ?? 'UNKNOWN')));
                            $status = (int) ($row['is_logged_in'] ?? 0) === 1 ? 'LOGIN' : 'LOGOUT';
                            ?>
                            <tr>
                                <td><?= esc((string) ($index + 1)) ?></td>
                                <td data-order="<?= esc($createdOrder) ?>"><?= esc($createdDateDisplay) ?></td>
                                <td><?= esc((string) ($row['username'] ?? '-')) ?></td>
                                <td>
                                    <span class="badge bg-label-info"><?= esc($eventType) ?></span>
                                </td>
                                <td>
                                    <?php if ($status === 'LOGIN'): ?>
                                        <span class="badge bg-label-success">LOGIN</span>
                                    <?php else: ?>
                                        <span class="badge bg-label-secondary">LOGOUT</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc((string) ($row['ip_address'] ?? '-')) ?></td>
                                <td><?= esc((string) ($row['ip_network'] ?? '-')) ?></td>
                                <td><?= esc((string) ($row['user_agent'] ?? '-')) ?></td>
                                <td><?= esc((string) ($row['notes'] ?? '-')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
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
        const datatableCssHref = '<?= base_url('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') ?>';

        if (!document.querySelector('link[data-role="datatable-bs5"]')) {
            const styleLink = document.createElement('link');
            styleLink.rel = 'stylesheet';
            styleLink.href = datatableCssHref;
            styleLink.setAttribute('data-role', 'datatable-bs5');
            document.head.appendChild(styleLink);
        }
    })();
</script>
<script src="<?= base_url('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') ?>"></script>
<script>
    (function () {
        if (!window.jQuery || !window.jQuery.fn || typeof window.jQuery.fn.DataTable !== 'function') {
            return;
        }

        window.jQuery('#loginHistoryTable').DataTable({
            pageLength: 25,
            lengthMenu: [10, 25, 50, 100],
            order: [[1, 'desc']],
            columnDefs: [
                { targets: 0, orderable: false, searchable: false }
            ],
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                infoEmpty: 'Menampilkan 0 sampai 0 dari 0 data',
                zeroRecords: 'Data tidak ditemukan',
                emptyTable: 'Belum ada data login history.',
                paginate: {
                    first: 'Awal',
                    last: 'Akhir',
                    next: 'Berikutnya',
                    previous: 'Sebelumnya'
                }
            }
        });
    })();
</script>
<?= $this->endSection() ?>
