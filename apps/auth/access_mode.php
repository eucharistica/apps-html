<?php
// apps/auth/access_mode.php

function emr_host(): string {
    $host = $_SERVER['HTTP_HOST'] ?? '';
    // buang port kalau ada (mis: 100.10.1.4:8080)
    $host = strtolower(trim(explode(':', $host)[0]));
    return $host;
}

function emr_is_private_ip(string $ip): bool {
    if (!$ip || !filter_var($ip, FILTER_VALIDATE_IP)) return false;

    // FILTER_FLAG_NO_PRIV_RANGE true => bukan private; jadi kita balik.
    // FILTER_FLAG_NO_RES_RANGE true => bukan reserved; kita balik.
    return !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
}

function emr_access_mode(): string {
    $host = emr_host();

    $publicDomains = [
        'website.rsudmatraman.my.id',
    ];

    if (in_array($host, $publicDomains, true)) {
        return 'PUBLIC_DOMAIN';
    }

    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    if (emr_is_private_ip($remote)) {
        return 'INTERNAL_DIRECT';
    }

    return 'UNKNOWN_PUBLIC';
}

