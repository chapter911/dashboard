<?php
$branding = $branding ?? [];
$appName = $appName ?? ($branding['app_name'] ?? 'Dashboard PLN');
?>

<div class="content-wrapper">
    <div class="container-fluid flex-grow-1 container-p-y">
        <?= $this->renderSection('content') ?>
    </div>

    <?= $this->include('layouts/partials/footer', ['appName' => $appName]) ?>
</div>
