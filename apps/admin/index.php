<?php
// apps/admin/index.php

require_once __DIR__ . '/../auth/guard.php';
require_once __DIR__ . '/../auth/rbac.php';
require_once __DIR__ . '/../config/bootstrap.php';

emr_require_login();
emr_require_permission('admin.access');

$root  = EMR_ROOT;
$asset = EMR_BASE_URL . 'assets/';

$page = $_GET['page'] ?? 'users';
$registry = require __DIR__ . '/registry.php';

if (!isset($registry[$page])) {
    header('Location: ' . EMR_BASE_URL . 'errors/404.php');
    exit;
}

$route = $registry[$page];
$permission = $route['permission'] ?? null;
if ($permission) {
    emr_require_permission($permission);
}

$emrtitle = $route['title'] ?? 'Admin';

// PASS ke global supaya bisa diakses di app.php
$GLOBALS['EMR_CONTENT_FILE'] = $route['file'] ?? null;$file;
include __DIR__ . '/layout/app.php';
