<?php

/**
 * Spiral Framework. Scaffolder
 *
 * Migrations component configuration file. Attention, configs might include runtime code which
 * depended on environment values only.
 *
 * @license MIT
 * @author  Valentin V (vvval)
 * @see     \Spiral\Migrations\Config\MigrationConfig
 */

declare(strict_types=1);

return [
    'directory' => directory('app') . 'migrations/',
    'database'  => 'runtime',
    'table'     => 'migrations',
    'safe'      => env('SPIRAL_ENV') === 'develop'
];
