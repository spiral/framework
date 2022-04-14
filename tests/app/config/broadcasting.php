<?php

declare(strict_types=1);

use Spiral\Broadcasting\Driver\LogBroadcast;
use Spiral\Broadcasting\Driver\NullBroadcast;

return [
    'default' => 'log',
    'aliases' => [
        'firebase' => 'null',
    ],
    'connections' => [
        'null' => [
            'driver' => NullBroadcast::class,
        ],
        'log' => [
            'driver' => LogBroadcast::class,
            'level' => \Psr\Log\LogLevel::DEBUG,
        ],
        'nullable' => [
            'type' => 'null',
        ],
    ],
    'driverAliases' => [
        'null' => NullBroadcast::class,
    ],
];
