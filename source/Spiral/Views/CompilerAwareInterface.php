<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */

namespace Spiral\Views;

use Spiral\Core\ContainerInterface;

/**
 * View class retrieving compiled filename from related compiler.
 */
interface CompilerAwareInterface extends ViewInterface
{
    /**
     * {@inheritdoc}
     */
    public function __construct(
        ContainerInterface $container,
        CompilerInterface $compiler,
        array $data = []
    );
}