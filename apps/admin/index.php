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
// Di awal index.php setelah $page assignment
error_log("Loading page: " . $page . " at " . date('Y-m-d H:i:s'));

$registry = require __DIR__ . '/registry.php';

if (!isset($registry[$page])) {
    // Fallback ke default jika page tidak ditemukan di registry
    $page = 'users'; // atau halaman default Anda
    if (!isset($registry[$page])) {
        header('Location: ' . EMR_BASE_URL . 'errors/404.php');
        exit;
    }
}

$route = $registry[$page];
$GLOBALS['EMR_PAGE_ASSETS'] = $route['assets'] ?? ['css' => [], 'js' => []];
$permission = $route['permission'] ?? null;
if ($permission) {
    emr_require_permission($permission);
}

// Tentukan content file path lebih eksplisit
$route = $registry[$page];
$permission = $route['permission'] ?? null;

if ($permission) {
    emr_require_permission($permission);
}

$emtitle = $route['title'] ?? 'Admin';

// PENTING: Set content file path sebelum include layout
if (isset($route['file'])) {
    $contentFilePath = realpath(__DIR__ . '/' . $route['file']);
    
    // Validasi path - jangan include file di luar direktori apps/admin
    $basePath = realpath(__DIR__);
    if ($contentFilePath && strpos($contentFilePath, $basePath) === 0 && file_exists($contentFilePath)) {
        $GLOBALS['EMR_CONTENT_FILE'] = $contentFilePath;
    } else {
        $GLOBALS['EMR_CONTENT_FILE'] = null;
    }
} else {
    $GLOBALS['EMR_CONTENT_FILE'] = null;
}

// Sekarang include layout
include __DIR__ . '/layout/app.php';
