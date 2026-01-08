<?php
require_once __DIR__ . '/../../../../config/bootstrap.php';
require_once __DIR__ . '/../../../../auth/rbac.php';

header('Content-Type: application/json');

if (!emr_can('admin.permissions.view')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden']);
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    emr_json_error('ID required', 400);
}

$pdo = emr_pdo();

$stmt = $pdo->prepare("SELECT id, name, created_at, updated_at FROM emr_permissions WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    emr_json_error('Permission not found', 404);
}

emr_json_success([
    'id' => (int)$row['id'],
    'name' => $row['name'],
    'created_at' => $row['created_at'],
    'updated_at' => $row['updated_at'],
]);
