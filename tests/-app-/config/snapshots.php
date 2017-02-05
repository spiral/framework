<?php
/**
 * Configuration options for Snapshot class. Attention, configs might include runtime code which
 * depended on environment values only.
 *
 * @see SnapshotConfig
 */

return [
    /*
     * View to render snapshot/exception information, please note that "slow" view will dump all
     * track arguments which can lead to much longer executing time.
     */
    'view'      => env('EXCEPTION_VIEW', 'spiral:exceptions/light/slow.php'),

    /*
     * Automatic snapshot reporting options. You can define your own SnapshotInterface in order
     * to create custom handler OR add Monolog handler to default log channel.
     */
    'reporting' => [
        'enabled'      => true,
        'maxSnapshots' => 20,
        'directory'    => directory('runtime') . 'snapshots',
        'filename'     => '{date}-{name}.html',
        'dateFormat'   => 'd.m.Y-Hi.s',
    ]
];