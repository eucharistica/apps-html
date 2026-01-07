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
                '/../apps/admin/pages/users/users.js',
                'plugins/custom/datatables/datatables.bundle.js',
                'js/custom/apps/user-management/users/list/table.js',
                'js/custom/apps/user-management/users/list/export-users.js',
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
    ],
];

