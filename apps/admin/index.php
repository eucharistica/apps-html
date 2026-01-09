<?php
// apps/admin/index.php

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../auth/rbac.php';

emr_require_login();
emr_require_permission('admin.access');

$root  = EMR_ROOT;
$asset = EMR_BASE_URL . 'assets/';

$page = $_GET['page'] ?? 'users';

// Optional debug log (aktifkan hanya kalau kamu punya constant/env)
if (defined('EMR_DEBUG') && EMR_DEBUG) {
    error_log("Admin page: " . $page . " at " . date('Y-m-d H:i:s'));
}

$registry = require __DIR__ . '/registry.php';

// fallback kalau page tidak ada
if (!isset($registry[$page])) {
    $page = 'users';
    if (!isset($registry[$page])) {
        header('Location: ' . EMR_BASE_URL . 'errors/404.php');
        exit;
    }
}

$route = $registry[$page];

// gate per page
if (!empty($route['permission'])) {
    emr_require_permission($route['permission']);
}

// assets untuk layout
$GLOBALS['EMR_PAGE_ASSETS'] = $route['assets'] ?? ['css' => [], 'js' => []];

// set title untuk layout
$emrtitle = $route['title'] ?? 'Admin';

// resolve & validate content file path
$GLOBALS['EMR_CONTENT_FILE'] = null;

if (!empty($route['file'])) {
    $contentFilePath = realpath(__DIR__ . '/' . $route['file']);
    $basePath = realpath(__DIR__);

    // wajib: file harus ada, dan masih berada di bawah folder apps/admin
    if ($contentFilePath && $basePath && strpos($contentFilePath, $basePath) === 0 && is_file($contentFilePath)) {
        $GLOBALS['EMR_CONTENT_FILE'] = $contentFilePath;
    }
}

// render layout
include __DIR__ . '/layout/app.php';
