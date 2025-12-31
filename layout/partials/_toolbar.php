<?php
// layout/partials/_toolbar.php
$__emr_root = defined('EMR_ROOT') ? EMR_ROOT : realpath(__DIR__ . '/../../');
?>
<!--begin::Toolbar-->
<div id="kt_app_toolbar" class="app-toolbar  py-7 pt-lg-15 pb-lg-5 ">
        <!--begin::Toolbar container-->
        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex align-items-stretch ">
                <!--begin::Toolbar container-->
                <div class="app-toolbar-container d-flex flex-column flex-row-fluid">

                        <?php include $__emr_root . '/layout/partials/toolbar/_page-title.php'; ?>

                        <!--begin::Toolbar wrapper--->
                        <div class="d-flex justify-content-between flex-wrap gap-4 gap-lg-10">

                                <?php include $__emr_root . '/layout/partials/toolbar/_menu.php'; ?>
                        </div>
                        <!--end::Toolbar wrapper--->

                </div>
                <!--end::Toolbar container--->
        </div>
        <!--end::Toolbar container-->
</div>
<!--end::Toolbar-->
