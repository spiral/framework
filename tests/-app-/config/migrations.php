<?php
/**
 * Migrations component configuration file. Attention, configs might include runtime code which
 * depended on environment values only.
 *
 * @see MigrationsConfig
 */
return [
    /*
     * Directory to store migration files
     */
    'directory' => directory('runtime') . 'migrations/',

    /*
     * Database name to store information about migrations status
     */
    'database'  => 'runtime',

    /*
     * Table name to store information about migrations status
     */
    'table'     => 'migrations',

    /*
     * When set to true no confirmation will be requested on migration run.
     */
    'safe'      => env('SPIRAL_ENV') == 'develop'
];