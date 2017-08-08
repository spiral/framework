<?php
/**
 * (c) Lev Seleznev (tuneyourserver) 2017
 */

namespace Spiral\Views;

/**
 * Class AbstractViewCache
 *
 * Provides ability to locate cache files by common prefix with many environments.
 *
 * @package Spiral\Views
 */
abstract class AbstractViewCache
{
    /**
     * Returns file name prefix by namespace:name
     *
     * @param string $name
     * @param string $namespace
     * @return string
     */
    protected function getPrefix($name, $namespace = 'default')
    {
        $prefix = $namespace . ViewManager::NS_SEPARATOR . $name;
        $prefix = preg_replace('/([^A-Za-z0-9]|-)+/', '-', $prefix) . '-' .
            hash('md5', $name . ViewManager::NS_SEPARATOR . $namespace);

        return $prefix;
    }
}