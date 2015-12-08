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
 * Configuration for Snapshot exception handler.
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
            'filename'     => '{date}-{exception}.html',
            'dateFormat'   => 'd.m.Y-Hi.s',
        ]
    ];

    /**
     * @return string
     */
    public function viewName()
    {
        return $this->config['view'];
    }

    /**
     * @return bool
     */
    public function reportingEnabled()
    {
        return $this->config['reporting']['enabled'];
    }

    /**
     * @return string
     */
    public function reportingDirectory()
    {
        return $this->config['reporting']['directory'];
    }

    /**
     * @return int
     */
    public function maxSnapshots()
    {
        return $this->config['reporting']['maxSnapshots'];
    }

    /**
     * @param \Throwable $exception
     * @param int        $time
     * @return string
     */
    public function snapshotFilename($exception, $time)
    {
        $name = (new \ReflectionObject($exception))->getShortName();

        $filename = \Spiral\interpolate($this->config['reporting']['filename'], [
            'date' => date($this->config['reporting']['dateFormat'], $time),
            'name' => $name
        ]);

        return $this->reportingDirectory() . $filename;
    }
}