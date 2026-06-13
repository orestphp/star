<?php

return [
    'paths' => [
        'migrations' => '/var/www/star/api/db/migrations',
        'seeds' => '/var/www/star/api/db/seeds'
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment' => 'development',
        'development' => [
            'adapter' => 'mysql',
            'host' => 'db',
            'name' => 'star',
            'user' => 'root',
            'pass' => 'root',
            'port' => '3306',
            'charset' => 'utf8mb4',
        ],
        'testing' => [
            'adapter' => 'mysql',
            'host' => 'db',
            'name' => 'star',
            'user' => 'root',
            'pass' => 'root',
            'port' => '3306',
            'charset' => 'utf8mb4',
        ]
    ],
    'version_order' => 'creation'

];
