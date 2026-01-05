<?php
// apps/simrs/layout/app.php
// Variables expected:
// - $root, $asset
// - $emr_tabs (array)
// - $emr_content (absolute file path)

?><!DOCTYPE html>
<html lang="en">

<head>
    <base href="" />
    <title><?= htmlspecialchars($emr_title ?? 'EMR SIMRS') ?></title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link rel="shortcut icon" href="<?= $asset ?>media/logos/favicon.ico" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />

    <link href="<?= $asset ?>plugins/custom/fullcalendar/fullcalendar.bundle.css" rel="stylesheet" type="text/css" />
    <link href="<?= $asset ?>plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="<?= $asset ?>css/style.bundle.css" rel="stylesheet" type="text/css" />

    <?php include $root . '/partials/trackers/_ga-tag-manager-for-head.php'; ?>

    <script>
        (function () {
            // allow running inside iframe for SIMRS tab system
            const allowIframe = new URLSearchParams(window.location.search).get('iframe') === '1';
            if (!allowIframe && window.top !== window.self) {
                window.top.location.replace(window.self.location.href);
            }
        })();
    </script>
</head>

<body id="kt_app_body" data-kt-app-page-loading-enabled="true" data-kt-app-page-loading="on"
    data-kt-app-header-fixed="true" data-kt-app-header-fixed-mobile="true" data-kt-app-toolbar-enabled="true"
    class="app-default">

    <?php include $root . '/partials/theme-mode/_init.php'; ?>
    <?php include $root . '/partials/trackers/_ga-tag-manager-for-body.php'; ?>
    <?php include $root . '/layout/partials/_page-loader.php'; ?>

    <?php
    // Inject toolbar tabs partial BEFORE layout default renders toolbar/content.
    // Layout will include its own toolbar; we override toolbar content by setting a global flag.
    $GLOBALS['EMR_TABS'] = $emr_tabs;
    $GLOBALS['EMR_CONTENT_FILE'] = $emr_content;
    ?>

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

                            <?php include __DIR__ . '/_content.php'; ?>

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


    <?php include $root . '/partials/_scrolltop.php'; ?>

    <script>var hostUrl = "<?= $asset ?>";</script>
    <script src="<?= $asset ?>plugins/global/plugins.bundle.js"></script>
    <script src="<?= $asset ?>js/scripts.bundle.js"></script>

    <script src="<?= $asset ?>plugins/custom/fullcalendar/fullcalendar.bundle.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/percent.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/radar.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/map.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/geodata/worldLow.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/geodata/continentsLow.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/geodata/usaLow.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/geodata/worldTimeZonesLow.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/geodata/worldTimeZonesLow.js"></script>

    <script src="<?= $asset ?>js/widgets.bundle.js"></script>
    <script src="<?= $asset ?>js/custom/widgets.js"></script>

</body>

</html>