<?php
require_once __DIR__ . '/../../../../config/bootstrap.php';
require_once __DIR__ . '/../../../../auth/rbac.php';

header('Content-Type: application/json');

// kalau belum punya permission khusus create, minimal: admin.access
if (!emr_can('admin.access')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

$pdo = emr_pdo();

$name = trim($_POST['permission_name'] ?? '');
$csrf = $_POST['_csrf'] ?? ''; // dipakai kalau sistem kamu validasi CSRF dari POST

if ($name === '') {
    emr_json_error('Permission name is required', 422);
}

try {
    // unique check
    $check = $pdo->prepare("SELECT id FROM emr_permissions WHERE name = ? LIMIT 1");
    $check->execute([$name]);
    if ($check->fetch()) {
        emr_json_error('Permission name already exists', 409);
    }

    $now = date('Y-m-d H:i:s');

    $stmt = $pdo->prepare("INSERT INTO emr_permissions (name, created_at, updated_at) VALUES (?, ?, ?)");
    $stmt->execute([$name, $now, $now]);

    emr_json_success([
        'id' => (int)$pdo->lastInsertId(),
        'name' => $name,
    ]);
} catch (Throwable $e) {
    error_log('create-permission error: ' . $e->getMessage());
    emr_json_error('Server error', 500);
}
