<?php
// apps/config/audit.php

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/bootstrap.php';

function emr_audit(string $action, ?string $message = null, array $meta = []): void
{
    try {
        $pdo = emr_pdo();

        $userId = $_SESSION['emr_user']['id'] ?? null;
        $ip = $meta['ip'] ?? ($_SERVER['REMOTE_ADDR'] ?? null);
        $ua = $meta['user_agent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? null);
        $uri = $meta['uri'] ?? ($_SERVER['REQUEST_URI'] ?? null);
        $method = $meta['method'] ?? ($_SERVER['REQUEST_METHOD'] ?? null);

        $stmt = $pdo->prepare("
            INSERT INTO emr_audit_logs (user_id, action, message, ip_address, user_agent, uri, method, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$userId, $action, $message, $ip, $ua, $uri, $method]);
    } catch (Throwable $e) {
        // jangan ganggu flow aplikasi kalau audit gagal
    }
}

function emr_audit_change(
    string $entityType,
    string $entityId,
    string $action,
    $before = null,
    $after = null,
    $diff = null,
    array $meta = []
): void
{
    try {
        $pdo = emr_pdo();

        $userId = $_SESSION['emr_user']['id'] ?? null;
        $ip = $meta['ip'] ?? ($_SERVER['REMOTE_ADDR'] ?? null);
        $ua = $meta['user_agent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? null);
        $uri = $meta['uri'] ?? ($_SERVER['REQUEST_URI'] ?? null);
        $method = $meta['method'] ?? ($_SERVER['REQUEST_METHOD'] ?? null);

        $beforeText = is_string($before) ? $before : ($before !== null ? json_encode($before, JSON_UNESCAPED_UNICODE) : null);
        $afterText  = is_string($after)  ? $after  : ($after  !== null ? json_encode($after, JSON_UNESCAPED_UNICODE) : null);
        $diffText   = is_string($diff)   ? $diff   : ($diff   !== null ? json_encode($diff, JSON_UNESCAPED_UNICODE) : null);

        $stmt = $pdo->prepare("
            INSERT INTO emr_audit_changes (user_id, entity_type, entity_id, action, before_text, after_text, diff_text, ip_address, user_agent, uri, method, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$userId, $entityType, $entityId, $action, $beforeText, $afterText, $diffText, $ip, $ua, $uri, $method]);
    } catch (Throwable $e) {
        // ignore
    }
}
