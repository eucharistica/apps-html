<?php
// layout/partials/_content.php
$contentFile = $GLOBALS['EMR_CONTENT_FILE'] ?? null;
$useIframeTabs = (bool)($GLOBALS['EMR_USE_IFRAME_TABS'] ?? false);
?>

<div id="kt_app_content" class="app-content">
    <?php if ($useIframeTabs): ?>
        <div id="emr-tab-content" class="flex-grow-1 position-relative" style="height: calc(100vh - 180px);">
            <!-- Iframes will be injected here -->
        </div>
    <?php else: ?>
        <?php if (is_string($contentFile) && file_exists($contentFile)): ?>
            <?php include $contentFile; ?>
        <?php else: ?>
            <div class="alert alert-info">No content.</div>
        <?php endif; ?>
    <?php endif; ?>
</div>
