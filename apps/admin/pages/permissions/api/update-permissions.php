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
$name = trim($_POST['permission_name'] ?? '');
$csrf = $_POST['_csrf'] ?? '';

if (!$id) emr_json_error('permission_id is required', 400);
if ($name === '') emr_json_error('Permission name is required', 422);

try {
    // exists?
    $exists = $pdo->prepare("SELECT id FROM emr_permissions WHERE id = ? LIMIT 1");
    $exists->execute([$id]);
    if (!$exists->fetch()) {
        emr_json_error('Permission not found', 404);
    }

    // unique except self
    $check = $pdo->prepare("SELECT id FROM emr_permissions WHERE name = ? AND id <> ? LIMIT 1");
    $check->execute([$name, $id]);
    if ($check->fetch()) {
        emr_json_error('Permission name already exists', 409);
    }

    $now = date('Y-m-d H:i:s');

    $upd = $pdo->prepare("UPDATE emr_permissions SET name = ?, updated_at = ? WHERE id = ?");
    $upd->execute([$name, $now, $id]);

    emr_json_success(['message' => 'Permission updated']);
} catch (Throwable $e) {
    error_log('update-permission error: ' . $e->getMessage());
    emr_json_error('Server error', 500);
}
