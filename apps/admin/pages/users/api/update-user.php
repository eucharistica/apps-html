<?php
// apps/admin/pages/users/api/update-user.php

require_once __DIR__ . '/../../../../config/bootstrap.php';
require_once __DIR__ . '/../../../../auth/rbac.php';

// Validasi CSRF
$token = $_POST['_csrf'] ?? null;
if (!emr_csrf_validate($token)) {
    return_json_error('Invalid CSRF token', 403);
}

header('Content-Type: application/json');

if (!emr_can('admin.users.edit')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

$user_id = $_POST['user_id'] ?? null;
$name = trim($_POST['name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$status = $_POST['status'] ?? 'active';
$role_id = $_POST['role_id'] ?? null;

if (!$user_id || !$name || !$username) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$pdo = emr_pdo();

try {
    // Build update query
    $updates = ['name = ?', 'username = ?', 'email = ?', 'status = ?', 'updated_at = NOW()'];
    $params = [$name, $username, $email ?: null, $status];

    if ($password) {
        $updates[] = 'password = ?';
        $params[] = password_hash($password, PASSWORD_BCRYPT);
    }

    $params[] = $user_id;

    $stmt = $pdo->prepare("UPDATE emr_users SET " . implode(', ', $updates) . " WHERE id = ?");
    $stmt->execute($params);

    // Update role
    $stmt = $pdo->prepare("DELETE FROM emr_user_has_roles WHERE user_id = ?");
    $stmt->execute([$user_id]);

    if ($role_id) {
        $stmt = $pdo->prepare("INSERT INTO emr_user_has_roles (user_id, role_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $role_id]);
    }

    emr_audit('admin.user_updated', 'Updated user ID: ' . $user_id);

    echo json_encode(['success' => true, 'message' => 'User updated']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
