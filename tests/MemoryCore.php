<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Tests;

use Spiral\Core\Core;
use Spiral\Core\CoreException;

class MemoryCore extends Core
{
    /**
     * Set of components to be pre-loaded before bootstrap method. By default spiral load Loader, Modules and I18n components.
     *
     * @var array
     */
    protected $autoload = array();

    /**
     * Pre-defined configs.
     *
     * @var array
     */
    protected $configs = array(
        'debug' => array(
            'loggers'   => array(
                'containers' => array()
            ),
            'backtrace' => array(
                'view'      => 'spiral:exception.dark',
                'snapshots' => array(
                    'enabled'    => false,
                    'timeFormat' => 'd.m.Y-Hi.s',
                    'directory'  => null
                )
            )
        )
    );

    /**
     * Runtime data.
     *
     * @var array
     */
    protected $data = array();

    /**
     * Bootstrapping. Most of code responsible for routes, events and other application preparations should located in this
     * method.
     */
    public function bootstrap()
    {
        //Nothing to do
    }

    /**
     * Load data previously saved to application cache, if file is not exists null will be returned. This method can be
     * replaced by Core Traits to use different ways to store data like APC (this was already done as experiment).
     *
     * @param string $name  Filename without .php
     * @param string $directory Application cache directory will be used by default.
     * @param string $realPath  Generated file location will be stored in this variable.
     * @return mixed
     */
    public function loadData($name, $directory = null, &$realPath = null)
    {
        return isset($this->data[$directory . $name]) ? $this->data[$directory . $name] : null;
    }

    /**
     * Save runtime data to application cache, previously saved file can be removed or rewritten at any moment. Cache is
     * determined by current applicationID and different for different environments. This method can be replaced by Core
     * Traits to use different ways to store data like APC (this was already done as experiment).
     *
     * All data stored using var_export() function, be aware of having to many write requests, however read will be optimized
     * by PHP using OPCache.
     *
     * File permission specified in File::RUNTIME to make file readable and writable for both web and CLI sessions.
     *
     * @param string $name  Filename without .php
     * @param mixed  $data      Data to be stored, any format supported by var_export().
     * @param string $directory Application cache directory will be used by default.
     * @return bool|string
     */
    public function saveData($name, $data, $directory = null)
    {
        $this->data[$directory . $name] = $data;
    }

    /**
     * Set config content.
     *
     * @param string $config
     * @param array  $data
     * @return static
     */
    public function setConfig($config, array $data)
    {
        $this->configs[$config] = $data;

        return $this;
    }

    /**
     * Load configuration files specified in application config directory. Config file may have extension, locked under
     * Core::getEnvironment() directory, this section will replace original config while application is under giver
     * environment. All config files with merged environment stored under cache directory.
     *
     * @param string $config Config filename (no .php)
     * @return mixed|array
     * @throws CoreException
     */
    public function getConfig($config)
    {
        if (!isset($this->configs[$config]))
        {
            throw new CoreException("Unable to load '{$config}' configuration, file not found.");
        }

        return $this->configs[$config];
    }
}