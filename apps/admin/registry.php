<?php
// apps/admin/registry.php

return [
    'users' => [
        'title' => 'Users',
        'permission' => 'admin.users.view',
        'file' => './pages/users/list.php',  // Path relative dari index.php
    ],
    'roles' => [
        'title' => 'Roles',
        'permission' => 'admin.roles.view',
        'file' => './pages/roles/list.php',
    ],
    'permissions' => [
        'title' => 'Permissions',
        'permission' => 'admin.permissions.view',
        'file' => './pages/permissions/list.php',
    ],
];

