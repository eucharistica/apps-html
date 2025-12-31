<?php
// apps/simrs/tabs.php

require_once __DIR__ . '/../auth/guard.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';

emr_require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

// Optional CSRF for tabs endpoint
$token = (string)($_POST['_token'] ?? '');
if ($token !== '' && !emr_csrf_validate($token)) {
    http_response_code(403);
    exit;
}

$action = (string)($_POST['action'] ?? '');
$tabKey = (string)($_POST['tab_key'] ?? '');
$returnJson = (string)($_POST['return'] ?? '') === '1';

try {
    $pdo = emr_pdo();
    $userId = (int)($_SESSION['emr_user']['id'] ?? 0);

    if ($action === 'close') {
        if ($tabKey === '') {
            http_response_code(400);
            exit;
        }

        // Load info of the tab being closed
        $stmt = $pdo->prepare("SELECT id, is_active, sort_order FROM emr_user_tabs WHERE user_id = ? AND tab_key = ? LIMIT 1");
        $stmt->execute([$userId, $tabKey]);
        $tab = $stmt->fetch();

        $wasActive = $tab ? ((int)$tab['is_active'] === 1) : false;
        $sort = $tab ? (int)$tab['sort_order'] : null;

        // Delete tab
        $stmt = $pdo->prepare("DELETE FROM emr_user_tabs WHERE user_id = ? AND tab_key = ?");
        $stmt->execute([$userId, $tabKey]);

        $redirect = null;

        if ($wasActive) {
            // Browser-like behavior: go to nearest left; if none, go to nearest right
            $stmt = $pdo->prepare("SELECT id, url FROM emr_user_tabs WHERE user_id = ? AND sort_order < ? ORDER BY sort_order DESC LIMIT 1");
            $stmt->execute([$userId, $sort]);
            $target = $stmt->fetch();

            if (!$target) {
                $stmt = $pdo->prepare("SELECT id, url FROM emr_user_tabs WHERE user_id = ? AND sort_order > ? ORDER BY sort_order ASC LIMIT 1");
                $stmt->execute([$userId, $sort]);
                $target = $stmt->fetch();
            }

            $pdo->prepare("UPDATE emr_user_tabs SET is_active = 0 WHERE user_id = ?")->execute([$userId]);

            if ($target && !empty($target['id'])) {
                $pdo->prepare("UPDATE emr_user_tabs SET is_active = 1 WHERE id = ?")->execute([$target['id']]);
                $redirect = $target['url'] ?? null;
            } else {
                $redirect = EMR_SIMRS_HOME;
            }
        }

        if ($returnJson) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => true, 'redirect' => $redirect]);
            exit;
        }

        echo 'OK';
        exit;
    }

    if ($action === 'close_other') {
        if ($tabKey === '') {
            http_response_code(400);
            exit;
        }

        $pdo->prepare("DELETE FROM emr_user_tabs WHERE user_id = ? AND tab_key <> ?")->execute([$userId, $tabKey]);
        $pdo->prepare("UPDATE emr_user_tabs SET is_active = 0 WHERE user_id = ?")->execute([$userId]);
        $pdo->prepare("UPDATE emr_user_tabs SET is_active = 1 WHERE user_id = ? AND tab_key = ?")->execute([$userId, $tabKey]);

        if ($returnJson) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => true, 'redirect' => null]);
            exit;
        }

        echo 'OK';
        exit;
    }

    if ($action === 'close_all') {
        $pdo->prepare("DELETE FROM emr_user_tabs WHERE user_id = ?")->execute([$userId]);

        $redirect = EMR_BASE_URL . 'apps/simrs/index.php?page=dashboard';

        if ($returnJson) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => true, 'redirect' => $redirect]);
            exit;
        }

        echo 'OK';
        exit;
    }

    http_response_code(400);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    exit;
}
