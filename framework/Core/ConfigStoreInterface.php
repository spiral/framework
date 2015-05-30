<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core;

interface ConfigStoreInterface
{
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