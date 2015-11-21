<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Views;

/**
 * Can be applied to compiled source to apply some modifications or optimizations.
 */
interface ProcessorInterface
{
    /**
     * @param string $source
     * @param string $compiledFilename
     * @return string
     */
    public function process($source, $compiledFilename = null);
}