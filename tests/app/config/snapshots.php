<?php
/**
 * Configuration options for Snapshot class. Attention, configs might include runtime code which depended
 * on environment values only.
 *
 * @see SnapshotConfig
 */

return [
    'view'      => env('EXCEPTION_VIEW', 'spiral:exceptions/light/slow.php'),
    'reporting' => [
        'enabled'      => true,
        'maxSnapshots' => 20,
        'directory'    => directory('runtime') . 'snapshots/',
        'filename'     => '{date}-{name}.html',
        'dateFormat'   => 'd.m.Y-Hi.s',
    ]
];