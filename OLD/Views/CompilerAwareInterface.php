<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Views;

use Spiral\Core\ContainerInterface;

/**
 * View class retrieving compiled filename from related compiler.
 */
interface CompilerAwareInterface extends ViewInterface
{
    /**
     * @param ContainerInterface $container
     * @param CompilerInterface  $compiler
     * @param array              $data
     */
    public function __construct(
        ContainerInterface $container,
        CompilerInterface $compiler,
        array $data = []
    );
}