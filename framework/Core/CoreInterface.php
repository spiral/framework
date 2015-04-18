<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core;

use Spiral\Core\Dispatcher\ClientException;

interface CoreInterface
{
    /**
     * Extension to use to runtime data and configuration cache files.
     */
    const RUNTIME_EXTENSION = '.php';

    /**
     * Call controller method by fully specified or short controller name, action and addition
     * options such as default controllers namespace, default name and postfix.
     *
     * @param string $controller Controller name, or class, or name with namespace prefix.
     * @param string $action     Controller action, empty by default (controller will use default action).
     * @param array  $parameters Additional methods parameters.
     * @return mixed
     * @throws ClientException
     * @throws CoreException
     */
    public function callAction($controller, $action = '', array $parameters = array());

    /**
     * Load data previously saved to application cache, if file is not exists null will be returned.
     * This method can be replaced by Core Traits to use different ways to store data like APC.
     *
     * @param string $filename  Filename without .php
     * @param string $directory Application cache directory will be used by default.
     * @param string $realPath  Generated file location will be stored in this variable.
     * @return mixed|array
     */
    public function loadData($filename, $directory = null, &$realPath = null);

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
     * @param string $filename  Filename without .php
     * @param mixed  $data      Data to be stored, any format supported by var_export().
     * @param string $directory Application cache directory will be used by default.
     * @return bool|string
     */
    public function saveData($filename, $data, $directory = null);

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
    public function loadConfig($config);
}