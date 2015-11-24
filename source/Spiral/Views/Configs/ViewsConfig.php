<?php
/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Anton Titov (Wolfy-J)
 */
namespace Spiral\Views\Configs;

use Spiral\Core\InjectableConfig;
use Spiral\Views\Engines;

/**
 * Translation component configuration.
 */
class ViewsConfig extends InjectableConfig
{
    /**
     * Configuration section.
     */
    const CONFIG = 'views';

    /**
     * @var array
     */
    protected $config = [
        'cache'       => [
            'enabled'   => false,
            'directory' => '/tmp'
        ],
        'namespaces'  => [],
        'environment' => [],
        'engines'     => []
    ];

    /**
     * @return array
     */
    public function getNamespaces()
    {
        return $this->config['namespaces'];
    }

    /**
     * @return array
     */
    public function environmentDependencies()
    {
        return $this->config['environment'];
    }

    /**
     * @return bool
     */
    public function cacheEnabled()
    {
        return $this->config['cache']['enabled'];
    }

    /**
     * @return string
     */
    public function cacheDirectory()
    {
        return $this->config['cache']['directory'];
    }

    /**
     * @return array
     */
    public function getEngines()
    {
        return array_keys($this->config['engines']);
    }

    /**
     * @param string $engine
     * @return bool
     */
    public function hasEngine($engine)
    {
        return isset($this->config['engines'][$engine]);

    }

    /**
     * @param string $engine
     * @return string
     */
    public function engineClass($engine)
    {
        return $this->config['engines'][$engine]['class'];
    }

    /**
     * @param string $engine
     * @return string
     */
    public function engineExtension($engine)
    {
        return $this->config['engines'][$engine]['extension'];
    }

    /**
     * @param string $engine
     * @return array
     */
    public function engineParameters($engine)
    {
        return $this->config['engines'][$engine];
    }
}