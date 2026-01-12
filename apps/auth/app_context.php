<?php
// apps/auth/app_context.php

function emr_path(): string {
    return (string)parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH); // safe path-only [web:580]
}

function emr_app_type_from_path(): string {
    $p = emr_path();

    if (strpos($p, '/apps/admin/') === 0) return 'admin';
    if (strpos($p, '/apps/simrs/') === 0) return 'simrs';

    // launcher / general pages
    return 'home';
}
