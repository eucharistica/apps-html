<?php
require_once __DIR__ . '/../../../../config/bootstrap.php';
require_once __DIR__ . '/../../../../auth/rbac.php';

header('Content-Type: application/json');

if (!emr_can('admin.access')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

$pdo = emr_pdo();

$id = $_POST['permission_id'] ?? null;
$csrf = $_POST['_csrf'] ?? '';

if (!$id) emr_json_error('permission_id is required', 400);

try {
    // relasi role_has_permissions diasumsikan FK cascade; kalau tidak ada, tetap aman delete manual dulu.
    $stmt = $pdo->prepare("DELETE FROM emr_permissions WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() < 1) {
        emr_json_error('Permission not found', 404);
    }

    emr_json_success(['message' => 'Permission deleted']);
} catch (Throwable $e) {
    error_log('delete-permission error: ' . $e->getMessage());
    emr_json_error('Server error', 500);
}
