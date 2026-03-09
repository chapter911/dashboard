<!-- Log File List -->
<h2>Log Files</h2>
<ul>
<?php foreach ($files as $file): ?>
        <li>
                <a href="#" class="log-link" data-filename="<?= esc($file) ?>"><?= esc($file) ?></a>
        </li>
<?php endforeach; ?>
</ul>

<!-- Modal -->
<div class="modal fade" id="logModal" tabindex="-1" aria-labelledby="logModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logModalLabel">Log File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <pre id="logModalContent" style="background:#222;color:#fff;padding:1em;overflow:auto;max-height:600px;"></pre>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.log-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
                e.preventDefault();
                var filename = this.getAttribute('data-filename');
                var modal = new bootstrap.Modal(document.getElementById('logModal'));
                var modalLabel = document.getElementById('logModalLabel');
                var modalContent = document.getElementById('logModalContent');
                modalLabel.textContent = 'Log File: ' + filename;
                modalContent.textContent = 'Loading...';
                fetch('<?= site_url('logviewer/show/') ?>' + filename)
                        .then(function(response) { return response.text(); })
                        .then(function(html) {
                                // Extract content from response
                                var contentMatch = html.match(/<pre[^>]*>([\s\S]*?)<\/pre>/);
                                modalContent.textContent = contentMatch ? contentMatch[1] : 'Tidak ada data.';
                        });
                modal.show();
        });
});
</script>
