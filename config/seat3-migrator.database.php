<?php

// config for Seatplus/ClassName
return [
    'seat3_backup' => [
        'driver' => 'mysql',
        'url' => env('DATABASE_URL'),
        'host' =>"seatBackup",
        'port' => env('DB_PORT', '3306'),
        'database' => 'seat_backup',
        'username' => env('DB_USERNAME', 'forge'),
        'password' => env('DB_PASSWORD', ''),
        'unix_socket' => env('DB_SOCKET', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => null,
        'options' => extension_loaded('pdo_mysql') ? array_filter([
            PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
        ]) : [],
    ],
];
