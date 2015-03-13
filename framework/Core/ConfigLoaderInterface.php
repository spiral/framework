<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core;

interface ConfigLoaderInterface
{
    /**
     * Load environment specific configuration.
     *
     * @param string $config Config name.
     * @return array
     * @throws CoreException
     */
    public function loadConfig($config);
}