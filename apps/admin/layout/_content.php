<?php
$contentFile = $GLOBALS['EMR_CONTENT_FILE'] ?? null;
if (is_string($contentFile) && file_exists($contentFile)):
    // include $contentFile;
else:
?>
    <div class="card">
        <div class="card-header border-0 pt-6">
            <div class="card-title">Test Content</div>
        </div>
        <div class="card-body">
            <p>File not found: <?= htmlspecialchars($contentFile ?? 'undefined') ?></p>
        </div>
    </div>
<?php endif; ?>
