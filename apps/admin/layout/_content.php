<?php
// apps/admin/layout/_content.php
$contentFile = $emrcontent ?? $GLOBALS['EMR_CONTENT_FILE'] ?? null;
?>

<div id="kt_app_content" class="app-content">
    <?php if (is_string($contentFile) && file_exists($contentFile)): ?>
        <?php include $contentFile; ?>
    <?php else: ?>
        <div class="alert alert-warning">
            Content not found.<br>
            Path: <?= htmlspecialchars($contentFile ?? 'undefined') ?>
        </div>
    <?php endif; ?>
</div>
