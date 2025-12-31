<?php
// layout/partials/_content.php
$__emr_root = defined('EMR_ROOT') ? EMR_ROOT : realpath(__DIR__ . '/../../');

$contentFile = $GLOBALS['EMR_CONTENT_FILE'] ?? null;
?>

<div id="emr-tab-content" class="flex-grow-1 position-relative" style="height: calc(100vh - 180px);">
    <!-- Iframes will be injected here -->
</div>