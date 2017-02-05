<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Modules;

use Spiral\Modules\Exceptions\RegistratorException;

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
     * @param string $id Wrapper string must identify what module added configuration lines. In
     *                   some future wrappers can be used to un-register modules.
     * @param array  $lines
     *
     * @throws RegistratorException
     */
    public function configure(string $config, string $placeholder, string $id, array $lines);
}