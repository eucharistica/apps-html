<?php
// apps/simrs/index.php

require_once __DIR__ . '/../auth/rbac.php';
require_once __DIR__ . '/../config/bootstrap.php';

emr_require_login();

$pdo = emr_pdo();
$userId = (int)($_SESSION['emr_user']['id'] ?? 0);

// Registry: page => [title, file, permission]
$registry = require __DIR__ . '/registry.php';

/**
 * Jika masuk ke shell tanpa page yang eksplisit:
 * redirect ke tab yang is_active=1 di DB supaya hard reload kembali ke tab terakhir.
 */
$pageParam = $_GET['page'] ?? null;
$pageParam = is_string($pageParam) ? trim($pageParam) : null;

if ($pageParam === null || $pageParam === '') {
    try {
        $stmt = $pdo->prepare("SELECT tab_key FROM emr_user_tabs WHERE user_id = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$userId]);
        $activeTabKey = $stmt->fetchColumn();
                    // Guard: Jika user belum punya data tabs, buat default dashboard
            if (empty($activeTabKey)) {
                // Cek apakah ada tab yang bisa digunakan sebagai default
                $defaultTabStmt = $pdo->prepare("SELECT id, tab_key, title, url FROM emr_user_tabs WHERE user_id = ? LIMIT 1");
                $defaultTabStmt->execute([$userId]);
                $defaultTab = $defaultTabStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($defaultTab) {
                    // Jika ada tab, gunakan sebagai default dan redirect
                    header('Location: ' . EMR_BASE_URL . 'apps/simrs/index.php?page=' . urlencode($defaultTab['tab_key']));
                    exit;
                } else {
                    // Jika belum ada tab sama sekali, buat default dashboard tab
                    $insertStmt = $pdo->prepare("INSERT INTO emr_user_tabs (user_id, tab_key, title, url, is_active, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
                    $insertStmt->execute([
                        $userId,
                        'dashboard',
                        'Dashboard',
                        'apps/simrs/pages/dashboard.php',
                        1,
                        1
                    ]);
                    // Redirect ke dashboard default
                    header('Location: ' . EMR_BASE_URL . 'apps/simrs/index.php?page=dashboard');
                    exit;
                }
            }

        if (is_string($activeTabKey) && $activeTabKey !== '' && isset($registry[$activeTabKey])) {
            header('Location: ' . EMR_BASE_URL . 'apps/simrs/index.php?page=' . urlencode($activeTabKey));
            exit;
        }
    } catch (Throwable $e) {
        // ignore
    }

    // fallback
    header('Location: ' . EMR_BASE_URL . 'apps/simrs/index.php');
    exit;
}

$page = (string)$pageParam;

if (!isset($registry[$page])) {
    header('Location: ' . EMR_ERROR_404);
    exit;
}

$route = $registry[$page];
$permission = $route['permission'] ?? null;
if ($permission) {
    emr_require_permission($permission);
}

// Save tab state (upsert + set active)
try {
    // deactivate others
    $stmt = $pdo->prepare("UPDATE emr_user_tabs SET is_active = 0 WHERE user_id = ?");
    $stmt->execute([$userId]);

    // upsert current
    $stmt = $pdo->prepare(
        "INSERT INTO emr_user_tabs (user_id, tab_key, title, url, is_active, sort_order)
         VALUES (?, ?, ?, ?, 1,
            COALESCE((SELECT MAX(t.sort_order) + 1 FROM emr_user_tabs t WHERE t.user_id = ?), 1)
         )
         ON DUPLICATE KEY UPDATE title=VALUES(title), url=VALUES(url), is_active=1"
    );

    $url = EMR_BASE_URL . 'apps/simrs/index.php?page=' . urlencode($page) . '&iframe=1';
    $stmt->execute([$userId, $page, $route['title'], $url, $userId]);
} catch (Throwable $e) {
    // ignore tab persistence errors to not block app
}

// Load tabs for toolbar
$tabs = [];
try {
    $stmt = $pdo->prepare("SELECT tab_key, title, url, is_active FROM emr_user_tabs WHERE user_id = ? ORDER BY sort_order ASC");
    $stmt->execute([$userId]);
    $tabs = $stmt->fetchAll();
} catch (Throwable $e) {
}

// Expose variables for layout partials

$root = EMR_ROOT;
$asset = EMR_BASE_URL . 'assets/';
$emr_tabs = $tabs;
$emr_page = $page;
$emr_title = $route['title'];
$emr_content = $route['file'];

if (($_GET['iframe'] ?? '') === '1') {
    // hanya render konten, tanpa layout metronic
    if (is_string($emr_content) && file_exists($emr_content)) {
        include $emr_content;
    } else {
        echo '<div class="alert alert-info">placeholder content</div>';
    }
    exit;
}

$GLOBALS['EMR_USE_IFRAME_TABS'] = true;
// render full layout dengan toolbar, tabs, dll
include $root . '/apps/simrs/layout/app.php';
