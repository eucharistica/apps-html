<?php
// apps/admin/pages/users/api/delete-user.php

require_once __DIR__ . '/../../../../config/bootstrap.php';
require_once __DIR__ . '/../../../../auth/rbac.php';

header('Content-Type: application/json');

// ================= CSRF =================
$token = $_POST['_csrf'] ?? null;
if (!emr_csrf_validate($token)) {
    emr_json_error('Invalid CSRF token', 403);
}

// ================= Permission =================
if (!emr_can('admin.users.delete')) {
    emr_json_error('Forbidden', 403);
}

// ================= Input =================
$user_id = $_POST['user_id'] ?? null;
$user_id = is_numeric($user_id) ? (int)$user_id : 0;

if ($user_id <= 0) {
    emr_json_error('User ID required');
}

// ================= Prevent self delete =================
$currentUserId = $_SESSION['emr_user']['id'] ?? null;
if ($user_id === (int)$currentUserId) {
    emr_json_error('Cannot delete your own account');
}

$pdo = emr_pdo();

try {
    $pdo->beginTransaction();

    // ================= Cek role admin =================
    $stmt = $pdo->prepare("
        SELECT 1 
        FROM emr_user_has_roles 
        WHERE user_id = ? AND role_id = 1
        LIMIT 1
    ");
    $stmt->execute([$user_id]);

    if ($stmt->fetchColumn()) {
        $pdo->rollBack();
        emr_json_error('Administrator account cannot be deleted');
    }

    // ================= Delete role relations =================
    $stmt = $pdo->prepare("DELETE FROM emr_user_has_roles WHERE user_id = ?");
    $stmt->execute([$user_id]);

    // ================= Delete user =================
    $stmt = $pdo->prepare("DELETE FROM emr_users WHERE id = ?");
    $stmt->execute([$user_id]);

    if ($stmt->rowCount() === 0) {
        $pdo->rollBack();
        emr_json_error('User not found');
    }

    $pdo->commit();

    // ================= Audit =================
    emr_audit('admin.user_deleted', 'Deleted user ID: ' . $user_id);

    emr_json_success([], 'User berhasil dihapus');

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    // log internal (jangan kirim ke client)
    error_log('[DELETE USER ERROR] ' . $e->getMessage());

    emr_json_error('Terjadi kesalahan saat menghapus user', 500);
}
