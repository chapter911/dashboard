<?php
$bulan = is_array($bulan ?? null) ? $bulan : [];
$unit = is_array($unit ?? null) ? $unit : [];
$data = is_array($data ?? null) ? $data : [];
$dataUid = is_array($data_uid ?? null) ? $data_uid : [];
$jenisSusut = (string) ($jenis_susut ?? 'netto');

$mapUnitMonth = [];
foreach ($data as $row) {
    $unitId = (int) ($row['unit_id'] ?? 0);
    $month = (int) date('n', strtotime((string) ($row['periode'] ?? '')));
    $mapUnitMonth[$unitId][$month] = $row;
}

$mapUidMonth = [];
foreach ($dataUid as $row) {
    $month = (int) date('n', strtotime((string) ($row['periode'] ?? '')));
    $mapUidMonth[$month] = $row;
}

$showNetto = in_array($jenisSusut, ['semua', 'netto'], true);
$showBruto = in_array($jenisSusut, ['semua', 'bruto'], true);

$fmt = static function ($value): string {
    if ($value === null || $value === '') {
        return '';
    }

    return number_format((float) $value, 2, ',', '.');
};
?>

<?php if ($showNetto): ?>
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h6 class="mb-0">Tabel Susut Netto</h6>
    </div>
    <div class="table-responsive text-nowrap">
        <table id="table-netto" class="table table-bordered table-striped mb-0">
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th>UP3</th>
                    <th>Jenis</th>
                    <?php foreach ($bulan as $b): ?>
                        <th class="text-center"><?= esc((string) ($b['singkatan'] ?? '')) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; ?>
                <?php foreach ($unit as $u): ?>
                    <?php $unitId = (int) ($u['unit_id'] ?? 0); ?>
                    <tr>
                        <td class="text-center" rowspan="2"><?= $no++ ?></td>
                        <td rowspan="2"><?= esc((string) ($u['unit_name'] ?? '')) ?></td>
                        <td>Netto</td>
                        <?php foreach ($bulan as $b): ?>
                            <?php $month = (int) ($b['bulan'] ?? 0); ?>
                            <?php $cell = $mapUnitMonth[$unitId][$month] ?? []; ?>
                            <td class="text-end"><?= $fmt($cell['netto_tt'] ?? null) ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <td>Akumulasi Netto</td>
                        <?php foreach ($bulan as $b): ?>
                            <?php $month = (int) ($b['bulan'] ?? 0); ?>
                            <?php $cell = $mapUnitMonth[$unitId][$month] ?? []; ?>
                            <td class="text-end"><?= $fmt($cell['netto_cumulative_tt'] ?? null) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td class="text-center" rowspan="2"><?= $no++ ?></td>
                    <td rowspan="2">UID</td>
                    <td>Netto</td>
                    <?php foreach ($bulan as $b): ?>
                        <?php $month = (int) ($b['bulan'] ?? 0); ?>
                        <?php $cell = $mapUidMonth[$month] ?? []; ?>
                        <td class="text-end"><?= $fmt($cell['persentase'] ?? null) ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td>Akumulasi Netto</td>
                    <?php foreach ($bulan as $b): ?>
                        <?php $month = (int) ($b['bulan'] ?? 0); ?>
                        <?php $cell = $mapUidMonth[$month] ?? []; ?>
                        <td class="text-end"><?= $fmt($cell['akumulasi_persentase'] ?? null) ?></td>
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if ($showBruto): ?>
<div class="card shadow-sm">
    <div class="card-header">
        <h6 class="mb-0">Tabel Susut Bruto</h6>
    </div>
    <div class="table-responsive text-nowrap">
        <table id="table-bruto" class="table table-bordered table-striped mb-0">
            <thead>
                <tr>
                    <th class="text-center">No</th>
                    <th>UP3</th>
                    <th>Jenis</th>
                    <?php foreach ($bulan as $b): ?>
                        <th class="text-center"><?= esc((string) ($b['singkatan'] ?? '')) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; ?>
                <?php foreach ($unit as $u): ?>
                    <?php $unitId = (int) ($u['unit_id'] ?? 0); ?>
                    <tr>
                        <td class="text-center" rowspan="2"><?= $no++ ?></td>
                        <td rowspan="2"><?= esc((string) ($u['unit_name'] ?? '')) ?></td>
                        <td>Bruto</td>
                        <?php foreach ($bulan as $b): ?>
                            <?php $month = (int) ($b['bulan'] ?? 0); ?>
                            <?php $cell = $mapUnitMonth[$unitId][$month] ?? []; ?>
                            <td class="text-end"><?= $fmt($cell['bruto_tt'] ?? null) ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <td>Akumulasi Bruto</td>
                        <?php foreach ($bulan as $b): ?>
                            <?php $month = (int) ($b['bulan'] ?? 0); ?>
                            <?php $cell = $mapUnitMonth[$unitId][$month] ?? []; ?>
                            <td class="text-end"><?= $fmt($cell['bruto_cumulative_tt'] ?? null) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td class="text-center" rowspan="2"><?= $no++ ?></td>
                    <td rowspan="2">UID</td>
                    <td>Bruto</td>
                    <?php foreach ($bulan as $b): ?>
                        <?php $month = (int) ($b['bulan'] ?? 0); ?>
                        <?php $cell = $mapUidMonth[$month] ?? []; ?>
                        <td class="text-end"><?= $fmt($cell['persentase'] ?? null) ?></td>
                    <?php endforeach; ?>
                </tr>
                <tr>
                    <td>Akumulasi Bruto</td>
                    <?php foreach ($bulan as $b): ?>
                        <?php $month = (int) ($b['bulan'] ?? 0); ?>
                        <?php $cell = $mapUidMonth[$month] ?? []; ?>
                        <td class="text-end"><?= $fmt($cell['akumulasi_persentase'] ?? null) ?></td>
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

