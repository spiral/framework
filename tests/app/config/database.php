<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

use Spiral\Database\Driver;

return [
    'default'   => 'default',
    'databases' => [
        'default' => ['driver' => 'runtime'],
    ],
    'drivers'   => [
        'runtime' => [
            'driver'     => Driver\SQLite\SQLiteDriver::class,
            'connection' => 'sqlite::memory:',
            'profiling'  => true,
        ],
        'other'   => [
            'driver'     => Driver\SQLite\SQLiteDriver::class,
            'connection' => 'sqlite::broken:',
            'profiling'  => true,
        ],
    ]
];