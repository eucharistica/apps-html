<?php
// apps/home/registry.php

return [
    'admin' => [
        'title' => 'Admin',
        'subtitle' => 'Kelola user, role, permission',
        'icon' => 'ki-outline ki-setting-2',
        'url' => 'apps/admin/index.php',
        'permission' => 'admin.access',
        'order' => 5,
    ],

    'simrs' => [
        'title' => 'SIMRS',
        'subtitle' => 'Pelayanan klinis',
        'icon' => 'ki-outline ki-key-square',
        'url' => 'apps/simrs/index.php',
        'permission' => 'simrs.dashboard.view',
        'order' => 10,
    ],

    'ekinerja' => [
        'title' => 'eKinerja',
        'subtitle' => 'Kinerja pegawai',
        'icon' => 'ki-outline ki-chart-line',
        'url' => 'apps/ekinerja/index.php',
        'permission' => null,
        'order' => 20,
    ],

    'pengadaan' => [
        'title' => 'Pengadaan',
        'subtitle' => 'Barang & jasa',
        'icon' => 'ki-outline ki-basket',
        'url' => 'apps/pengadaan/index.php',
        'permission' => null,
        'order' => 30,
    ],
];
