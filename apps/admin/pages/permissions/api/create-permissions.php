<?php
require_once __DIR__ . '/../../../../config/bootstrap.php';
require_once __DIR__ . '/../../../../auth/rbac.php';

header('Content-Type: application/json');

emr_require_permission_api('admin.permissions.create');

$pdo = emr_pdo();

$name = trim($_POST['permission_name'] ?? '');
$csrf = $_POST['_token'] ?? '';

if ($name === '') {
  emr_json_error('Permission name is required', 422);
  exit;
}

// Hard rule biar konsisten dengan registry: lowercase + dot/underscore/dash saja.
if (!preg_match('/^[a-z0-9._-]+$/', $name)) {
  emr_json_error('Invalid permission name format. Use a-z 0-9 . _ - only', 422);
  exit;
}

try {
  // unique check
  $check = $pdo->prepare("SELECT id FROM emr_permissions WHERE name = ? LIMIT 1");
  $check->execute([$name]);
  if ($check->fetch(PDO::FETCH_ASSOC)) {
    emr_json_error('Permission name already exists', 409);
    exit;
  }

  $now = date('Y-m-d H:i:s');
  $stmt = $pdo->prepare("INSERT INTO emr_permissions (name, created_at, updated_at) VALUES (?, ?, ?)");
  $stmt->execute([$name, $now, $now]);

  echo json_encode([
    'success' => true,
    'message' => 'Permission berhasil dibuat',
    'data' => [
      'id' => (int)$pdo->lastInsertId(),
      'name' => $name,
    ],
  ]);
  exit;

} catch (Throwable $e) {
  error_log('create-permission error: ' . $e->getMessage());
  emr_json_error('Server error', 500);
}
