<?php

declare(strict_types=1);

return [
    'cache'        => [
        'enabled'   => false,
        'directory' => __DIR__ . '/../cache',
    ],
    'namespaces'   => [
        'default'    => [__DIR__ . '/../fixtures/default'],
        'other'      => [__DIR__ . '/../fixtures/other'],
        'extensions' => [__DIR__ . '/../fixtures/other/extensions'],
    ],
    'dependencies' => [],
    'engines'      => [],
];
