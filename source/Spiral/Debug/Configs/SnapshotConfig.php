<?php
/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Anton Titov (Wolfy-J)
 */

namespace Spiral\Debug\Configs;

use Spiral\Core\InjectableConfig;

/**
 * Configuration for Snapshot exception handler. This handler can render exception trace and other
 * debug information in a pretty form (depends on an associated view).
 */
class SnapshotConfig extends InjectableConfig
{
    /**
     * Configuration section.
     */
    const CONFIG = 'snapshots';

    /**
     * @var array
     */
    protected $config = [
        'view'      => '',
        'reporting' => [
            'enabled'      => true,
            'maxSnapshots' => 20,
            'directory'    => '',
            'filename'     => '{date}-{name}.html',
            'dateFormat'   => 'd.m.Y-Hi.s',
        ]
    ];

    /**
     * View name to be used for snapshot rendering.
     *
     * @return string
     */
    public function viewName(): string
    {
        return $this->config['view'];
    }

    /**
     * Do we need to report any errors? Turn off if you want to keep your snapshot folder empty
     * in development.
     *
     * @return bool
     */
    public function reportingEnabled(): bool
    {
        return $this->config['reporting']['enabled'];
    }

    /**
     * Where to store generated snapshots.
     *
     * @return string
     */
    public function reportingDirectory(): string
    {
        return rtrim($this->config['reporting']['directory'], '/') . '/';
    }

    /**
     * @return int
     */
    public function maxSnapshots(): int
    {
        return $this->config['reporting']['maxSnapshots'];
    }

    /**
     * Generate filename for snaphot.
     *
     * @param \Throwable $exception
     * @param int        $time
     *
     * @return string
     */
    public function snapshotFilename(\Throwable $exception, int $time): string
    {
        $name = (new \ReflectionObject($exception))->getShortName();

        $filename = \Spiral\interpolate($this->config['reporting']['filename'], [
            'date' => date($this->config['reporting']['dateFormat'], $time),
            'name' => $name
        ]);

        return $this->reportingDirectory() . $filename;
    }
}