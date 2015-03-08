<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core\Plugins;

use Spiral\Core\CoreException;

trait ConfigCacheTrait
{
    /**
     * Preloaded configs cache. Using common memory cache for all configs will speed up application, but every
     * config change has to manually reset this cache. Attention, average memory consumption will increase.
     *
     * This plugin is unnecessary to use on small applications with low amount of used components.
     *
     * @var array
     */
    protected $configCache = array();

    /**
     * Load configuration files specified in application config directory. Config file may have extension, locked under
     * Core::getEnvironment() directory, this section will replace original config while application is under giver
     * environment. All config files with merged environment stored under cache directory.
     *
     * @param string $config Config filename (no .php)
     * @return mixed|array
     * @throws CoreException
     */
    public function loadConfig($config)
    {
        if (!$this->configCache)
        {
            $this->configCache = $this->loadData('cache-config');
            if (!is_array($this->configCache))
            {
                $this->configCache = array();
            }
        }

        if (isset($this->configCache[$config]))
        {
            return $this->configCache[$config];
        }

        //First time load
        $this->configCache[$config] = $data = parent::loadConfig($config);

        //Caching
        $this->saveData('cache-config', $this->configCache);

        return $data;
    }
}
