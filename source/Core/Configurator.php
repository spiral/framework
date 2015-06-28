<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core;

class Configurator implements ConfiguratorInterface
{
    /**
     * Predefined configurations.
     *
     * @var array
     */
    protected $configs = [];

    /**
     * Set of configurations declared by component name or config container.
     *
     * Example:
     *
     * new Configurator([
     *  'dbal'  => [...],
     *  'cache' => [...]
     * ]);
     *
     * @param array $configs
     */
    public function __construct(array $configs)
    {
        $this->configs = $configs;
    }

    /**
     * Load configuration files specified in application config directory. Config file may have
     * extension, locked under Core->getEnvironment() directory, this section will replace original
     * config while application is under giver environment. All config files with merged environment
     * stored under cache directory.
     *
     * @param string $config Config filename (no .php)
     * @return array
     * @throws CoreException
     */
    public function getConfig($config)
    {
        if (!isset($this->configs[$config]))
        {
            throw new CoreException("Undefined config '{$config}'.");
        }

        return $this->configs[$config];
    }

    /**
     * Update configuration under desired name.
     *
     * @param string $name
     * @param array  $config
     */
    public function setConfig($name, array $config)
    {
        $this->configs[$name] = $config;
    }
}