<?php

use App\Models\User;

return [
    'accounts' => [
        'super_admin' => [
            'name' => 'super admin',
            'email' => env('SUPER_ADMIN_EMAIL', 'superadmin@gmail.com'),
            'password' => env('SUPER_ADMIN_PASSWORD'),
            'password_env' => 'SUPER_ADMIN_PASSWORD',
            'role' => User::ROLE_SUPER_ADMIN,
        ],
        'admin' => [
            'name' => 'admin',
            'email' => env('ADMIN_EMAIL', 'admin@gmail.com'),
            'password' => env('ADMIN_PASSWORD'),
            'password_env' => 'ADMIN_PASSWORD',
            'role' => User::ROLE_ADMIN,
        ],
    ],
];
