<?php
// apps/admin/pages/users/api/get-users.php

require_once __DIR__ . '/../../../../config/bootstrap.php';
require_once __DIR__ . '/../../../../auth/rbac.php';

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

// 1) ambil user
$stmt = $pdo->prepare("
    SELECT id, username, name, email, status
    FROM emr_users
    WHERE id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    emr_json_error('User not found', 404);
}

// 2) ambil roles (multi)
$stmt = $pdo->prepare("
    SELECT ur.role_id, r.name
    FROM emr_user_has_roles ur
    INNER JOIN emr_roles r ON r.id = ur.role_id
    WHERE ur.user_id = ?
    ORDER BY r.name ASC
");
$stmt->execute([$id]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$user['role_ids'] = array_map(fn($x) => (string)$x['role_id'], $rows);
$user['roles'] = array_map(fn($x) => $x['name'], $rows);

// default display
$user['name']  = $user['name'] ?? 'N/A';
$user['email'] = $user['email'] ?? 'N/A';

emr_json_success($user);
