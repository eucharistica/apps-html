<?php
// layout/partials/_content.php
$__emr_root = defined('EMR_ROOT') ? EMR_ROOT : realpath(__DIR__ . '/../../');

$contentFile = $GLOBALS['EMR_CONTENT_FILE'] ?? null;
?>
<!--begin::Content-->
<div id="kt_app_content" class="app-content ">

    <?php if (is_string($contentFile) && file_exists($contentFile)) : ?>
        <?php include $contentFile; ?>
    <?php else: ?>
        <div class="alert alert-info">placeholder content</div>
    <?php endif; ?>

</div>
<!--end::Content-->
