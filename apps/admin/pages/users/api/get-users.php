<?php
// apps/admin/pages/users/api/get-users.php

require_once __DIR__ . '/../../../../config/bootstrap.php';
require_once __DIR__ . '/../../../../auth/rbac.php';
require_once EMR_ROOT . '/apps/config/response.php';

header('Content-Type: application/json');

if (!emr_can('admin.users.view')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

$pdo = emr_pdo();
$id  = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID required']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT
        u.id,
        u.username,
        u.name,
        u.email,
        u.status,
        ur.role_id,
        r.name AS role_name
    FROM emr_users u
    LEFT JOIN emr_user_has_roles ur ON ur.user_id = u.id
    LEFT JOIN emr_roles r ON r.id = ur.role_id
    WHERE u.id = ?
    LIMIT 1
");

$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

error_log('DEBUG get-users: user=' . json_encode($user));

if (!$user) {
    emr_json_error('User not found', 404);
}

// Default values (kalau memang mau dipaksa ada)
$user['name']  = $user['name'] ?? 'N/A';
$user['email'] = $user['email'] ?? 'N/A';

emr_json_success($user);
