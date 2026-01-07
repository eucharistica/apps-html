<?php
// apps/admin/layout/app.php

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

$root = $root ?? (defined('EMR_ROOT') ? EMR_ROOT : realpath(__DIR__ . '/../../..'));
$asset = $asset ?? (defined('EMR_BASE_URL') ? EMR_BASE_URL . 'assets/' : '/assets/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <base href="" />
    <title><?= htmlspecialchars($emrtitle ?? 'Admin') ?> - EMR</title>
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
            const allowIframe = new URLSearchParams(window.location.search).get('iframe') === '1';
            if (!allowIframe && window.top !== window.self) {
                window.top.location.replace(window.self.location.href);
            }
        })();
    </script>

    
    <?php
    $pageAssets = $GLOBALS['EMR_PAGE_ASSETS'] ?? ['css' => [], 'js' => []];
    ?>

    <?php foreach (($pageAssets['css'] ?? []) as $css): ?>
    <link href="<?= htmlspecialchars($asset . ltrim($css, '/')) ?>" rel="stylesheet" type="text/css" />
    <?php endforeach; ?>
</head>

<body id="kt_app_body"
      data-kt-app-page-loading-enabled="true"
      data-kt-app-page-loading="on"
      data-kt-app-header-fixed="true"
      data-kt-app-header-fixed-mobile="true"
      data-kt-app-toolbar-enabled="true"
      class="app-default">

<?php include $root . '/partials/theme-mode/_init.php'; ?>
<?php include $root . '/partials/trackers/_ga-tag-manager-for-body.php'; ?>
<?php include $root . '/layout/partials/_page-loader.php'; ?>

<?php
$__emr_root = $root;
$GLOBALS['EMR_TABS'] = null;
$GLOBALS['EMR_USE_IFRAME_TABS'] = true;
$__admin_registry = require __DIR__ . '/../registry.php';
$__admin_menu = [];
foreach ($__admin_registry as $key => $item) {
    $__admin_menu[] = [
        'key' => $key,
        'title' => $item['title'] ?? '-',
        'url' => EMR_BASE_URL . 'apps/admin/index.php?page=' . urlencode($key),
    ];
}
$GLOBALS['EMR_ADMIN_MENU'] = $__admin_menu;
?>

<!--begin::App-->
<div class="d-flex flex-column flex-root app-root" id="kt_app_root">
    <!--begin::Page-->
    <div class="app-page flex-column flex-column-fluid" id="kt_app_page">

        <?php include $__emr_root . '/layout/partials/_header.php'; ?>

        <!--begin::Wrapper-->
        <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">

            <!--begin::Admin Toolbar (Simple nav, bukan iframe tabs)-->
            <div class="app-toolbar py-3 py-lg-6 px-4 px-lg-8">
                <div class="app-container container-xxl d-flex align-items-center">
                    <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-6 fw-semibold" role="tablist">
                        <?php foreach ($__admin_menu as $item): ?>
                                <?php $isActive = ($_GET['page'] ?? 'users') === $item['key']; ?>
                                <li class="nav-item" role="presentation">
                                    <a href="<?= htmlspecialchars($item['url']) ?>" 
                                       class="nav-link <?= $isActive ? 'active' : '' ?>" 
                                       role="tab">
                                        <?= htmlspecialchars($item['title']) ?>
                                    </a>
                                </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <!--end::Admin Toolbar-->

            <!--begin::Wrapper container-->
            <div class="app-container container-xxl">

                <!--begin::Main-->
                <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
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
<script>
    window.EMR = {
        baseUrl: "<?= EMR_BASE_URL ?>"
    };
</script>
<script src="<?= $asset ?>plugins/global/plugins.bundle.js"></script>
<script src="<?= $asset ?>js/scripts.bundle.js"></script>

<?php foreach (($pageAssets['js'] ?? []) as $js): ?>
  <script src="<?= htmlspecialchars($asset . ltrim($js, '/')) ?>"></script>
<?php endforeach; ?>
</body>
</html>
