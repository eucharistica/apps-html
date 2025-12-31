<?php
require_once __DIR__ . '/../../auth/guard.php';
require_once __DIR__ . '/../../auth/rbac.php';

emr_require_login();

// Home/Dashboard permission
emr_require_permission('simrs.dashboard.view');

$root = EMR_ROOT;
$asset = EMR_BASE_URL . 'assets/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <base href="" />
    <title>EMR SIMRS</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <link rel="shortcut icon" href="<?= $asset ?>media/logos/favicon.ico" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />

    <link href="<?= $asset ?>plugins/custom/fullcalendar/fullcalendar.bundle.css" rel="stylesheet" type="text/css" />
    <link href="<?= $asset ?>plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="<?= $asset ?>css/style.bundle.css" rel="stylesheet" type="text/css" />

    <?php include $root . '/partials/trackers/_ga-tag-manager-for-head.php'; ?>

    <script>
        if (window.top != window.self) {
            window.top.location.replace(window.self.location.href);
        }
    </script>
</head>

<body id="kt_app_body" data-kt-app-page-loading-enabled="true" data-kt-app-page-loading="on"
      data-kt-app-header-fixed="true" data-kt-app-header-fixed-mobile="true" data-kt-app-toolbar-enabled="true"
      class="app-default">

    <?php include $root . '/partials/theme-mode/_init.php'; ?>
    <?php include $root . '/partials/trackers/_ga-tag-manager-for-body.php'; ?>
    <?php include $root . '/layout/partials/_page-loader.php'; ?>
    <?php include $root . '/layout/_default.php'; ?>
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

    <script src="<?= $asset ?>js/widgets.bundle.js"></script>
    <script src="<?= $asset ?>js/custom/widgets.js"></script>
    <script src="<?= $asset ?>js/custom/apps/chat/chat.js"></script>
    <script src="<?= $asset ?>js/custom/utilities/modals/upgrade-plan.js"></script>
</body>
</html>
