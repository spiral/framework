<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Views;

/**
 * Provides ability to modify source given by source.
 */
interface ModifierInterface
{
    /**
     * Modify given source.
     *
     * @param string $source    Source.
     * @param string $namespace View namespace.
     * @param string $name      View name (no extension included).
     * @return mixed
     */
    public function modify($source, $namespace, $name);
}