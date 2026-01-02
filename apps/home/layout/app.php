<?php
// apps/home/layout/app.php
// Variables expected: $root, $asset, $emr_title, $emrcontent

$root  = $root  ?? (defined('EMR_ROOT') ? EMR_ROOT : realpath(__DIR__ . '/../../..'));
$asset = $asset ?? (defined('EMR_BASE_URL') ? EMR_BASE_URL . 'assets/' : '/assets/');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <base href="" />
    <title><?= htmlspecialchars($emr_title ?? 'EMR') ?></title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link rel="shortcut icon" href="<?= $asset ?>media/logos/favicon.ico" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />

    <link href="<?= $asset ?>plugins/custom/fullcalendar/fullcalendar.bundle.css" rel="stylesheet" type="text/css" />
    <link href="<?= $asset ?>plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="<?= $asset ?>css/style.bundle.css" rel="stylesheet" type="text/css" />

    <?php include $root . '/partials/trackers/_ga-tag-manager-for-head.php'; ?>
</head>

<body id="kt_app_body"
      data-kt-app-page-loading-enabled="true"
      data-kt-app-page-loading="on"
      data-kt-app-header-fixed="true"
      data-kt-app-header-fixed-mobile="true"
      data-kt-app-toolbar-enabled="false"
      class="app-default">

<?php include $root . '/partials/theme-mode/_init.php'; ?>
<?php include $root . '/partials/trackers/_ga-tag-manager-for-body.php'; ?>
<?php include $root . '/layout/partials/_page-loader.php'; ?>

<?php
$__emr_root = $root;
$GLOBALS['EMR_TABS'] = null;
$GLOBALS['EMR_USE_IFRAME_TABS'] = false;
$GLOBALS['EMR_CONTENT_FILE'] = $emrcontent ?? null;
?>

<!--begin::App-->
<div class="d-flex flex-column flex-root app-root" id="kt_app_root">
    <!--begin::Page-->
    <div class="app-page flex-column flex-column-fluid" id="kt_app_page">

        <?php include $__emr_root . '/layout/partials/_header.php'; ?>

        <!--begin::Wrapper-->
        <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
            <!--begin::Wrapper container-->
            <div class="app-container container-xxl">
                <!--begin::Main-->
                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
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

<?php include $root . '/partials/_scrolltop.php'; ?>

<script>var hostUrl = "<?= $asset ?>";</script>
<script src="<?= $asset ?>plugins/global/plugins.bundle.js"></script>
<script src="<?= $asset ?>js/scripts.bundle.js"></script>

</body>
</html>
