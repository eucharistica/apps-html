<?php
// apps/admin/pages/users/api/get-users.php

require_once __DIR__ . '/../../../../../config/bootstrap.php';
require_once __DIR__ . '/../../../../../auth/rbac.php';

header('Content-Type: application/json');

if (!emr_can('admin.users.view')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

$pdo = emr_pdo();
$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID required']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT u.id, u.username, u.name, u.email, u.status,
           ur.role_id
    FROM emr_users u
    LEFT JOIN emr_user_has_roles ur ON ur.user_id = u.id
    WHERE u.id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

echo json_encode(['success' => true, 'user' => $user]);
