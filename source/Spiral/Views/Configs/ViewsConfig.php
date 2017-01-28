<?php
/**
 * Spiral Framework.
 *
 * @license MIT
 * @author  Anton Titov (Wolfy-J)
 */

namespace Spiral\Views\Configs;

use Spiral\Core\InjectableConfig;

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
     * List of available view namespaces associated with list of directories.
     *
     * @return array
     */
    public function getNamespaces(): array
    {
        return $this->config['namespaces'];
    }

    /**
     * @param string $namespace
     *
     * @return array
     */
    public function namespaceDirectories(string $namespace): array
    {
        return $this->config['namespaces'][$namespace];
    }

    /**
     * Environment dependencies.
     *
     * @return array
     */
    public function environmentDependencies(): array
    {
        return $this->config['environment'];
    }

    /**
     * @return bool
     */
    public function cacheEnabled(): bool
    {
        return $this->config['cache']['enabled'];
    }

    /**
     * @return string
     */
    public function cacheDirectory(): string
    {
        return rtrim($this->config['cache']['directory'], '/') . '/';
    }

    /**
     * List of available engine names.
     *
     * @return array
     */
    public function getEngines(): array
    {
        return array_keys($this->config['engines']);
    }

    /**
     * @param string $engine
     *
     * @return bool
     */
    public function hasEngine(string $engine): bool
    {
        return isset($this->config['engines'][$engine]);

    }

    /**
     * @param string $engine
     *
     * @return string
     */
    public function engineClass(string $engine): string
    {
        return $this->config['engines'][$engine]['class'];
    }

    /**
     * @param string $engine
     *
     * @return string
     */
    public function engineExtension(string $engine): string
    {
        return $this->config['engines'][$engine]['extension'];
    }

    /**
     * @param string $engine
     *
     * @return array
     */
    public function engineOptions(string $engine): array
    {
        return $this->config['engines'][$engine];
    }
}