<?php
require_once __DIR__ . '/access_mode.php';

function emr_client_ip_public(): string {
    $cf = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '';
    if ($cf && filter_var($cf, FILTER_VALIDATE_IP)) return $cf; // Cloudflare origin header [web:472]

    $xff = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
    if ($xff) {
        $parts = array_map('trim', explode(',', $xff));
        $candidate = $parts[0] ?? '';
        if ($candidate && filter_var($candidate, FILTER_VALIDATE_IP)) return $candidate;
    }

    return $_SERVER['REMOTE_ADDR'] ?? '';
}

function emr_can_login_by_access_mode(array $userRow): bool {
    $mode = emr_access_mode();

    // Internal direct (akses via IP/host internal + REMOTE_ADDR private) => bebas
    if ($mode === 'INTERNAL_DIRECT') return true;

    // Akses via domain publik atau unknown public => wajib allow_external_login=1
    $allow = (int)($userRow['allow_external_login'] ?? 0);
    return $allow === 1;
}
