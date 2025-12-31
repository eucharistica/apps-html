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

if ($action !== 'close' || $tabKey === '') {
    http_response_code(400);
    exit;
}

try {
    $pdo = emr_pdo();
    $userId = (int)($_SESSION['emr_user']['id'] ?? 0);

    // Was it active?
    $stmt = $pdo->prepare("SELECT is_active FROM emr_user_tabs WHERE user_id = ? AND tab_key = ? LIMIT 1");
    $stmt->execute([$userId, $tabKey]);
    $wasActive = (int)$stmt->fetchColumn() === 1;

    // Delete tab
    $stmt = $pdo->prepare("DELETE FROM emr_user_tabs WHERE user_id = ? AND tab_key = ?");
    $stmt->execute([$userId, $tabKey]);

    $redirect = null;

    if ($wasActive) {
        // Pick nearest tab by sort_order (prefer previous, else next)
        $stmt = $pdo->prepare("SELECT sort_order FROM emr_user_tabs WHERE user_id=? ORDER BY sort_order ASC LIMIT 1");
        $stmt->execute([$userId]);

        // choose last tab in list (simple) as active
        $stmt = $pdo->prepare("SELECT id, url FROM emr_user_tabs WHERE user_id = ? ORDER BY sort_order DESC LIMIT 1");
        $stmt->execute([$userId]);
        $row = $stmt->fetch();

        $pdo->prepare("UPDATE emr_user_tabs SET is_active = 0 WHERE user_id = ?")->execute([$userId]);

        if ($row && !empty($row['id'])) {
            $pdo->prepare("UPDATE emr_user_tabs SET is_active = 1 WHERE id = ?")->execute([$row['id']]);
            $redirect = $row['url'] ?? null;
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

} catch (Throwable $e) {
    http_response_code(500);
    exit;
}
