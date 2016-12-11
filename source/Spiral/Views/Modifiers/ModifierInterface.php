<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Views\Modifiers;

use Spiral\Views\EnvironmentInterface;

/**
 * Provides ability to modify source given by source.
 */
interface ModifierInterface
{
    /**
     * Modify given source.
     *
     * @param EnvironmentInterface $environment
     * @param string               $source    Source.
     * @param string               $namespace View namespace.
     * @param string               $name      View name (no extension included).
     *
     * @return string
     */
    public function modify(
        EnvironmentInterface $environment,
        string $source,
        string $namespace,
        string $name
    ): string;
}