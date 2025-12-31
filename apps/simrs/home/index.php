<?php
// apps/simrs/home/index.php
// Backward compatible: redirect to front controller
require_once __DIR__ . '/../../config/bootstrap.php';

header('Location: ' . EMR_BASE_URL . 'apps/simrs/index.php?page=dashboard');
exit;
