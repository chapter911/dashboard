<!-- Log File List -->
<h2>Log Files</h2>
<ul>
<?php foreach ($files as $file): ?>
    <li><a href="<?= site_url('logviewer/show/' . $file) ?>"><?= esc($file) ?></a></li>
<?php endforeach; ?>
</ul>
