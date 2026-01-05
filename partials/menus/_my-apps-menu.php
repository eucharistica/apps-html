<?php
$__emr_root = defined('EMR_ROOT') ? EMR_ROOT : realpath(__DIR__ . '/../../');
require_once $__emr_root . '/apps/app_registry.php';

$apps = emr_apps_registry();
$current = emr_current_app();
?>

<!--begin::My apps-->
<div class="menu menu-sub menu-sub-dropdown menu-column w-100 w-sm-350px" data-kt-menu="true">
    <!--begin::Card-->
    <div class="card">
        <!--begin::Card header-->
        <div class="card-header">
            <!--begin::Card title-->
            <div class="card-title">Aplikasi</div>
            <!--end::Card title-->
        </div>
        <!--end::Card header-->
        <!--begin::Card body-->
        <div class="card-body py-5">
            <!--begin::Scroll-->
            <div class="mh-450px scroll-y me-n5 pe-5">
                <!--begin::Row-->
                <div class="row g-2">
                    <?php foreach ($apps as $app): ?>
                        <?php
                          $isActive = ($current === ($app['key'] ?? ''));
                          $href = EMR_BASE_URL . ltrim(($app['url'] ?? '#'), '/');
                        ?>
                        <!--begin::Col-->
                        <div class="col-4">
                            <a href="<?= htmlspecialchars($href) ?>"
                                class="d-flex flex-column flex-center text-center text-gray-800 text-hover-primary bg-hover-light rounded py-4 px-3 mb-3 <?= $isActive ? 'bg-light-primary' : '' ?>">
                                <span class="symbol symbol-40px mb-2">
                                    <span class="symbol-label bg-light-<?= $isActive ? 'primary' : 'gray' ?>">
                                        <i class="<?= htmlspecialchars($app['icon'] ?? 'ki-outline ki-grid') ?> fs-2 text-<?= $isActive ? 'primary' : 'gray-600' ?>"></i>
                                    </span>
                                </span>
                                <span class="fw-semibold fs-7"><?= htmlspecialchars($app['title'] ?? '-') ?></span>
                            </a>
                        </div>
                        <!--end::Col-->
                    <?php endforeach; ?>
                </div>
                <!--end::Row-->
            </div>
            <!--end::Scroll-->
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->
</div>
<!--end::My apps-->
