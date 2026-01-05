<?php
// apps/admin/pages/users/api/delete-user.php

require_once __DIR__ . '/../../../../../config/bootstrap.php';
require_once __DIR__ . '/../../../../../auth/rbac.php';

header('Content-Type: application/json');

if (!emr_can('admin.users.delete')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

$user_id = $_POST['user_id'] ?? null;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'User ID required']);
    exit;
}

// Prevent deleting yourself
if ($user_id == ($_SESSION['emr_user']['id'] ?? null)) {
    echo json_encode(['success' => false, 'message' => 'Cannot delete your own account']);
    exit;
}

$pdo = emr_pdo();

try {
    $stmt = $pdo->prepare("DELETE FROM emr_user_has_roles WHERE user_id = ?");
    $stmt->execute([$user_id]);

    $stmt = $pdo->prepare("DELETE FROM emr_users WHERE id = ?");
    $stmt->execute([$user_id]);

    emr_audit('admin.user_deleted', 'Deleted user ID: ' . $user_id);

    echo json_encode(['success' => true, 'message' => 'User deleted']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
