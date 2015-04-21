<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core\Traits;

use Spiral\Core\CoreException;

trait ApcTrait
{
    /**
     * Load data previously saved to application cache, if file is not exists null will be returned.
     * This method can be replaced by Core Traits to use different ways to store data like APC.
     *
     * @param string $name      Filename without .php
     * @param string $directory Application cache directory will be used by default.
     * @param string $realPath  Generated file location will be stored in this variable.
     * @return mixed|array
     */
    public function loadData($name, $directory = null, &$realPath = null)
    {
        if (!function_exists('apc_exists'))
        {
            return parent::loadData($name, $directory, $realPath);
        }

        if (apc_exists($name))
        {
            $realPath = $this->makeFilename($name, $directory);

            return apc_fetch($name);
        }

        $data = parent::loadData($name, $directory, $realPath);
        if ($data !== null)
        {
            apc_store($name, $data);
        }

        return $data;
    }

    /**
     * Save runtime data to application cache, previously saved file can be removed or rewritten at
     * any moment. Cache is determined by current applicationID and different for different environments.
     * This method can be replaced by Core Traits to use different ways to store data like APC.
     *
     * All data stored using var_export() function, be aware of having to many write requests, however
     * read will be optimized by PHP using OPCache.
     *
     * File permission specified in File::RUNTIME to make file readable and writable for both web and
     * CLI sessions.
     *
     * @param string $name      Filename without .php
     * @param mixed  $data      Data to be stored, any format supported by var_export().
     * @param string $directory Application cache directory will be used by default.
     * @return bool|string
     */
    public function saveData($name, $data, $directory = null)
    {
        if (!function_exists('apc_exists'))
        {
            return parent::saveData($name, $data, $directory);
        }

        parent::saveData($name, $data, $directory);

        return (bool)apc_store($name, $data);
    }

    /**
     * Load configuration files specified in application config directory. Config file may have
     * extension, locked under Core::getEnvironment() directory, this section will replace original
     * config while application is under giver environment. All config files with merged environment
     * stored under cache directory.
     *
     * @param string $config Config filename (no .php)
     * @return array
     * @throws CoreException
     */
    public function loadConfig($config)
    {
        if (!function_exists('apc_exists'))
        {
            return parent::loadConfig($config);
        }

        $filename = self::$directories['config'] . '/' . $config . '.' . self::CONFIGS_EXTENSION;
        $cached = str_replace(array('/', '\\'), '-', 'config-' . $config);

        $data = $this->loadData($cached, null, $cachedFilename);
        if (!file_exists($filename))
        {
            throw new CoreException("Unable to load '{$config}' configuration, file not found.");
        }

        if (!file_exists($cachedFilename) || filemtime($cachedFilename) < filemtime($filename))
        {
            file_exists($cachedFilename) && unlink($cachedFilename);
            apc_delete($cached);

            //Configuration were updated, reloading
            return parent::loadConfig($config);
        }

        if (!apc_exists($cached))
        {
            $this->saveData($cached, $data);
        }

        return $data;
    }
}