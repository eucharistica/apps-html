<?php
// layout/_default.php
// NOTE: Use absolute includes to avoid include_path issues when called from nested apps/* scripts.

$__emr_root = defined('EMR_ROOT') ? EMR_ROOT : realpath(__DIR__ . '/..');
?>
<!--begin::App-->
<div class="d-flex flex-column flex-root app-root" id="kt_app_root">
    <!--begin::Page-->
    <div class="app-page  flex-column flex-column-fluid " id="kt_app_page">

        <?php include $__emr_root . '/layout/partials/_header.php'; ?>

        <!--begin::Wrapper-->
        <div class="app-wrapper  flex-column flex-row-fluid " id="kt_app_wrapper">

            <?php include $__emr_root . '/layout/partials/_toolbar.php'; ?>

            <!--begin::Wrapper container-->
            <div class="app-container container-xxl ">

                <!--begin::Main-->
                <div class="app-main flex-column flex-row-fluid " id="kt_app_main">
                    <!--begin::Content wrapper-->
                    <div class="d-flex flex-column flex-column-fluid">

                        <?php include $__emr_root . '/layout/partials/_content.php'; ?>

                    </div>
                    <!--end::Content wrapper-->

                    <?php include $__emr_root . '/layout/partials/_footer.php'; ?>
                </div>
                <!--end:::Main-->

            </div>
            <!--end::Wrapper container-->
        </div>
        <!--end::Wrapper-->

    </div>
    <!--end::Page-->
</div>
<!--end::App-->
