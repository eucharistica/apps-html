<?php
// apps/registry.php
return [
  'home' => [
    'title' => 'Home',
    'icon' => 'ki-outline ki-home',
    'url' => 'apps/home/index.php',
    'permission' => null,
    'order' => 10,
  ],
  'simrs' => [
    'title' => 'SIMRS',
    'icon' => 'ki-outline ki-key-square',
    'url' => 'apps/simrs/index.php',
    'permission' => 'simrs.dashboard.view',
    'order' => 20,
  ],
  'ekinerja' => [
    'title' => 'eKinerja',
    'icon' => 'ki-outline ki-chart-line',
    'url' => 'apps/ekinerja/index.php',
    'permission' => null,
    'order' => 30,
  ],
  'pengadaan' => [
    'title' => 'Pengadaan',
    'icon' => 'ki-outline ki-basket',
    'url' => 'apps/pengadaan/index.php',
    'permission' => null,
    'order' => 40,
  ],
];
