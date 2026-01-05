<?php
// apps/admin/pages/users/api/create-user.php

require_once __DIR__ . '/../../../../../config/bootstrap.php';
require_once __DIR__ . '/../../../../../auth/rbac.php';

header('Content-Type: application/json');

if (!emr_can('admin.users.create')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role_id = $_POST['role_id'] ?? null;

if (!$name || !$username || !$password) {
    echo json_encode(['success' => false, 'message' => 'Name, username, and password required']);
    exit;
}

$pdo = emr_pdo();
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

try {
    // Insert user
    $stmt = $pdo->prepare("
        INSERT INTO emr_users (username, name, email, password, status, created_at, updated_at)
        VALUES (?, ?, ?, ?, 'active', NOW(), NOW())
    ");
    $stmt->execute([$username, $name, $email ?: null, $hashedPassword]);
    $userId = $pdo->lastInsertId();

    // Assign role if provided
    if ($role_id) {
        $stmt = $pdo->prepare("INSERT INTO emr_user_has_roles (user_id, role_id) VALUES (?, ?)");
        $stmt->execute([$userId, $role_id]);
    }

    emr_audit('admin.user_created', 'Created user: ' . $username);

    echo json_encode(['success' => true, 'message' => 'User created']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
