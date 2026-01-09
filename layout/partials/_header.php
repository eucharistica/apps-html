<?php
// layout/partials/_header.php
$__emr_root = defined('EMR_ROOT') ? EMR_ROOT : realpath(__DIR__ . '/../../');
?>
<!--begin::Header-->
<div id="kt_app_header" class="app-header  align-items-stretch ">
    <!--begin::Header container-->
    <div class="app-container container-xxl d-flex align-items-stretch justify-content-between" id="kt_app_header_container">
        <!--begin::Header-->
        <div class="d-flex align-items-center justify-content-between flex-row-fluid" id="kt_app_header_wrapper">

            <?php include $__emr_root . '/layout/partials/header/__logo.php'; ?>

            <?php include $__emr_root . '/layout/partials/header/__menu.php'; ?>

            <?php include $__emr_root . '/layout/partials/header/__navbar.php'; ?>

        </div>
        <!--end::Header-->
    </div>
    <!--end::Header container-->
</div>
<!--end::Header-->
