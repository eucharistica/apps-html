<?php
// apps/admin/registry.php

return [
    'users' => [
        'title' => 'Users',
        'permission' => 'admin.users.view',
        'file' => __DIR__ . '/pages/users/list.php',
    ],
    'roles' => [
        'title' => 'Roles',
        'permission' => 'admin.roles.view',
        'file' => __DIR__ . '/pages/roles/list.php',
    ],
    'permissions' => [
        'title' => 'Permissions',
        'permission' => 'admin.permissions.view',
        'file' => __DIR__ . '/pages/permissions/list.php',
    ],
];
