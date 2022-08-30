<?php

declare(strict_types=1);

use Spiral\Cache\Storage\ArrayStorage;
use Spiral\Cache\Storage\FileStorage;

return [
    'default' => 'local',
    'aliases' => [
        'user-data' => 'file',
    ],
    'typeAliases' => [
        'array' => ArrayStorage::class,
    ],
    'storages' => [
        'local' => [
            'type' => ArrayStorage::class,
        ],
        'file' => [
            'type' => FileStorage::class,
            'path' => sys_get_temp_dir() . '/spiral/cache',
        ],
        'inMemory' => [
            'type' => 'array',
        ],
    ],
];
