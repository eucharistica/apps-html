<?php
// layout/partials/_toolbar.php
$__emr_root = defined('EMR_ROOT') ? EMR_ROOT : realpath(__DIR__ . '/../../');

// If SIMRS front controller provides tabs, render tabs toolbar
$tabs = $GLOBALS['EMR_TABS'] ?? null;
?>
<!--begin::Toolbar-->
<div id="kt_app_toolbar" class="app-toolbar  py-7 pt-lg-15 pb-lg-5 ">
    <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex align-items-stretch ">
        <div class="app-toolbar-container d-flex flex-column flex-row-fluid">

            <?php include $__emr_root . '/layout/partials/toolbar/_page-title.php'; ?>

            <div class="d-flex justify-content-between flex-wrap gap-4 gap-lg-10">
                <?php if (is_array($tabs)) : ?>
                    <?php include $__emr_root . '/apps/simrs/partials/_tabs.php'; ?>
                <?php else: ?>
                    <?php include $__emr_root . '/layout/partials/toolbar/_menu.php'; ?>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>
<!--end::Toolbar-->
