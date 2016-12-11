<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Views\Processors;

use Spiral\Views\EnvironmentInterface;

/**
 * Can be applied to compiled source to apply some modifications or optimizations. Processor outcome
 * might ONLY depend on environment values.
 */
interface ProcessorInterface
{
    /**
     * @param EnvironmentInterface $environment
     * @param string               $source
     * @param string               $namespace
     * @param string               $view
     * @param string               $cachedFilename
     *
     * @return string
     */
    public function process(
        EnvironmentInterface $environment,
        string $source,
        string $namespace,
        string $view,
        string $cachedFilename = null
    ): string;
}