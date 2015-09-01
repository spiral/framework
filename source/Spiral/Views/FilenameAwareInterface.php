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
 * View renderer without associated compilation engine.
 */
interface FilenameAwareInterface extends ViewInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $namespace
     * @param string             $view
     * @param string             $filename
     * @param array              $data
     */
    public function __construct(
        ContainerInterface $container,
        $namespace,
        $view,
        $filename,
        array $data = []
    );
}