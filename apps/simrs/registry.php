<?php
// apps/simrs/registry.php

return [
    'dashboard' => [
        'title' => 'Dashboard',
        'file' => __DIR__ . '/pages/dashboard.php',
        'permission' => 'simrs.dashboard.view',
    ],
    'rawat_jalan' => [
        'title' => 'Rawat Jalan',
        'file' => __DIR__ . '/pages/rawat-jalan.php',
        'permission' => 'simrs.rawat_jalan.view',
    ],
    'rawat_inap' => [
        'title' => 'Rawat Inap',
        'file' => __DIR__ . '/pages/rawat-inap.php',
        'permission' => 'simrs.rawat_inap.view',
    ],
];
