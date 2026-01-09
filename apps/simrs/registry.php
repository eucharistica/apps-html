<?php
// apps/simrs/registry.php

return [
    'dashboard' => [
        'title' => 'Dashboard',
        'group' => 'Dashboard',
        'icon' => 'ki-outline ki-home',
        'order' => 10,
        'open_mode' => 'inline', // inline | iframe
        'permission' => 'simrs.dashboard.view',
        'file' => __DIR__ . '/pages/dashboard.php',
    ],

    'rawat_jalan' => [
        'title' => 'Rawat Jalan',
        'group' => 'Pelayanan',
        'icon' => 'ki-outline ki-stethoscope',
        'order' => 20,
        'open_mode' => 'inline',
        'permission' => 'simrs.rawat_jalan.view',
        'file' => __DIR__ . '/pages/rawat-jalan.php',
    ],

    'rawat_inap' => [
        'title' => 'Rawat Inap',
        'group' => 'Pelayanan',
        'icon' => 'ki-outline ki-bed',
        'order' => 30,
        'open_mode' => 'inline',
        'permission' => 'simrs.rawat_inap.view',
        'file' => __DIR__ . '/pages/rawat-inap.php',
    ],

    // contoh iframe
    // 'rawat_jalan_external' => [
    //     'title' => 'Rawat Jalan (External)',
    //     'group' => 'Pelayanan',
    //     'icon' => 'ki-outline ki-stethoscope',
    //     'order' => 25,
    //     'open_mode' => 'iframe',
    //     'url' => 'apps/simrs/external/rawat-jalan/index.php',
    //     'permission' => 'simrs.rawat_jalan.view',
    // ],
];
