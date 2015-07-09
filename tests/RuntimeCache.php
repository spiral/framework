<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Tests;

use Spiral\Core\RuntimeCacheInterface;

class RuntimeCache implements RuntimeCacheInterface
{
    /**
     * Data to be stored or loaded.s
     *
     * @var array
     */
    protected $data = [];

    /**
     * Load data previously saved to application cache, if file is not exists null will be returned.
     * This method can be replaced by Core Traits to use different ways to store data like APC.
     *
     * @param string $name      Filename without .php
     * @param string $directory Application cache directory will be used by default.
     * @return mixed|array
     */
    public function loadData($name, $directory = null)
    {
        if (!isset($this->data[$directory . $name]))
        {
            return null;
        }

        return $this->data[$directory . $name];
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
     */
    public function saveData($name, $data, $directory = null)
    {
        $this->data[$directory . $name] = $data;
    }
}