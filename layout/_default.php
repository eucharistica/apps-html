<!--begin::App-->
<div class="d-flex flex-column flex-root app-root" id="kt_app_root">
    <!--begin::Page-->
    <div class="app-page  flex-column flex-column-fluid " id="kt_app_page">

        <?php include 'layout/partials/_header.php' ?>

        <!--begin::Wrapper-->
        <div class="app-wrapper  flex-column flex-row-fluid " id="kt_app_wrapper">

            <?php include 'layout/partials/_toolbar.php' ?>

            <!--begin::Wrapper container-->
            <div class="app-container container-xxl ">

                <!--begin::Main-->
                <div class="app-main flex-column flex-row-fluid " id="kt_app_main">
                    <!--begin::Content wrapper-->
                    <div class="d-flex flex-column flex-column-fluid">

                        <?php include 'layout/partials/_content.php' ?>

                    </div>
                    <!--end::Content wrapper-->

                    <?php include 'layout/partials/_footer.php' ?>
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