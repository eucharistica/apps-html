<?php
// apps/simrs/layout.php (excerpt) – ensure this is merged into your existing layout
?>

<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <?php include __DIR__ . '/partials/_tabs.php'; ?>
        <div id="emr-tab-content" class="flex-grow-1 position-relative" style="height: calc(100vh - 180px);">
            <!-- Iframes will be injected here -->
        </div>
    </div>
</div>
