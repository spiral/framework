<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Modules;

/**
 * Provides ability to safely edit content of existed configurations.
 */
interface RegistratorInterface
{
    /**
     * Update configuration file by adding set of lines in a place of specified placeholder.
     *
     * @param string $class
     * @param string $config
     * @param string $placeholder
     * @param array  $lines
     */
    public function configure($class, $config, $placeholder, array $lines);
}