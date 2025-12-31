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

if ($action !== 'close' || $tabKey === '') {
    http_response_code(400);
    exit;
}

try {
    $pdo = emr_pdo();
    $userId = (int)($_SESSION['emr_user']['id'] ?? 0);

    $stmt = $pdo->prepare("DELETE FROM emr_user_tabs WHERE user_id = ? AND tab_key = ?");
    $stmt->execute([$userId, $tabKey]);

    // If active tab removed, set last tab as active
    $stmt = $pdo->prepare("SELECT id FROM emr_user_tabs WHERE user_id = ? ORDER BY sort_order DESC LIMIT 1");
    $stmt->execute([$userId]);
    $lastId = $stmt->fetchColumn();

    if ($lastId) {
        $pdo->prepare("UPDATE emr_user_tabs SET is_active = 0 WHERE user_id = ?")->execute([$userId]);
        $pdo->prepare("UPDATE emr_user_tabs SET is_active = 1 WHERE id = ?")->execute([$lastId]);
    }

    echo 'OK';
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    exit;
}
