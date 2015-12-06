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
     * @param string $config
     * @param string $placeholder
     * @param string $wrapper Wrapper string must identify what module added configuration lines.
     *                        In some future wrappers can be used to un-register modules.
     * @param array  $lines
     */
    public function configure($config, $placeholder, $wrapper, array $lines);
}