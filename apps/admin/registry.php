<?php
// apps/admin/registry.php

return [
    'users' => [
        'title' => 'Users',
        'permission' => 'admin.users.view',
        'file' => './pages/users/list.php',
        'assets' => [
            'css' => [
                'plugins/custom/datatables/datatables.bundle.css',
            ],
            'js' => [
                'js/custom/admin/user-management/users/users.js',
                'plugins/custom/datatables/datatables.bundle.js',
            ],
        ],
    ],
    'roles' => [
        'title' => 'Roles',
        'permission' => 'admin.roles.view',
        'file' => './pages/roles/list.php',
        'assets' => [
            'css' => [
                'plugins/custom/datatables/datatables.bundle.css',
            ],
            'js' => [
                'plugins/custom/datatables/datatables.bundle.js',
                'js/custom/apps/user-management/users/list/table.js',
                'js/custom/apps/user-management/users/list/export-users.js',
            ],
        ],
    ],
    'permissions' => [
        'title' => 'Permissions',
        'permission' => 'admin.permissions.view',
        'file' => './pages/permissions/list.php',
        'assets' => [
            'css' => [
                'plugins/custom/datatables/datatables.bundle.css',
            ],
            'js' => [
                'js/custom/admin/user-management/permissions/permissions.js',
                'plugins/custom/datatables/datatables.bundle.js',
            ],
        ],
    ],
];

