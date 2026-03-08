<?php
$bulan = is_array($bulan ?? null) ? $bulan : [];
$unit = is_array($unit ?? null) ? $unit : [];
$dataTarget = is_array($data_target ?? null) ? $data_target : [];

$targetMap = [];
foreach ($dataTarget as $row) {
    $unitId = (int) ($row['unit_id'] ?? 0);
    $month = (int) ($row['bulan'] ?? 0);
    $targetMap[$unitId][$month] = (float) ($row['nilai'] ?? 0);
}
?>

<div class="table-responsive">
    <table class="table table-bordered table-striped" id="table-target-susut" style="width:100%;">
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>Unit</th>
                <?php foreach ($bulan as $b): ?>
                    <th class="text-center"><?= esc(strtoupper((string) ($b['singkatan'] ?? ''))) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; ?>
            <?php foreach ($unit as $u): ?>
                <?php $unitId = (int) ($u['unit_id'] ?? 0); ?>
                <tr>
                    <td class="text-center"><?= $no++ ?></td>
                    <td><?= esc((string) ($u['unit_name'] ?? '')) ?></td>
                    <?php foreach ($bulan as $b): ?>
                        <?php $month = (int) ($b['bulan'] ?? 0); ?>
                        <?php $val = $targetMap[$unitId][$month] ?? null; ?>
                        <td class="text-end"><?= $val === null ? '' : number_format((float) $val, 2, ',', '.') . '%' ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    $(function () {
        if ($.fn.DataTable) {
            if ($.fn.DataTable.isDataTable('#table-target-susut')) {
                $('#table-target-susut').DataTable().destroy();
            }

            $('#table-target-susut').DataTable({
                pageLength: 25,
                ordering: false,
                scrollX: true
            });
        }
    });
</script>
