<?php

declare(strict_types=1);

use Spiral\Views\Engine\Native\NativeEngine;

return [
    'cache' => [
        'enable' => false,
        'directory' => directory('cache') . 'views',
    ],
    'namespaces' => [
        'default' => [directory('views')],
    ],
    'dependencies' => [],
    'engines' => [
        NativeEngine::class,
    ],
    'globalVariables' => [
        'foo' => 'bar',
    ],
];
