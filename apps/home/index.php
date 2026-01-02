<?php
// apps/home/index.php
require_once __DIR__ . '/../auth/guard.php';
require_once __DIR__ . '/../auth/rbac.php';
require_once __DIR__ . '/../config/bootstrap.php';

emr_require_login();

$root  = EMR_ROOT;
$asset = EMR_BASE_URL . 'assets/';

$apps = require __DIR__ . '/registry.php';

$filtered = [];
foreach ($apps as $key => $app) {
    $perm = $app['permission'] ?? null;
    if ($perm && !emr_can($perm)) continue;
    $filtered[$key] = $app + ['key' => $key];
}

usort($filtered, fn($a,$b) => ((int)($a['order'] ?? 9999)) <=> ((int)($b['order'] ?? 9999)));

$GLOBALS['EMR_HOME_APPS'] = $filtered;

$emr_title = 'Home';
$emrcontent = __DIR__ . '/page.php';

include __DIR__ . '/layout/app.php';
