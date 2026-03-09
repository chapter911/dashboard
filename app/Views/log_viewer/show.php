<!-- Log File Content -->
<h2>Log File: <?= esc($filename) ?></h2>
<pre style="background:#222;color:#fff;padding:1em;overflow:auto;max-height:600px;">
<?= esc($content) ?>
</pre>
<a href="<?= site_url('logviewer') ?>">Back to log list</a>
