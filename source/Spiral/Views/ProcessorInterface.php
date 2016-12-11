<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Views;

/**
 * Can be applied to source before or after compilation to apply some modifications or
 * optimizations. Processor outcome might ONLY depend on environment values.
 */
interface ProcessorInterface
{
    /**
     * @param EnvironmentInterface $environment
     * @param string               $source
     * @param string               $namespace
     * @param string               $view
     *
     * @return string
     */
    public function modify(
        EnvironmentInterface $environment,
        string $source,
        string $namespace,
        string $view
    ): string;
}