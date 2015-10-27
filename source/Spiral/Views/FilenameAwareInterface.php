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
 * View renderer without associated compilation engine.
 */
interface FilenameAwareInterface extends ViewInterface
{
    /**
     * @param ContainerInterface $container
     * @param ViewManager        $views
     * @param string             $namespace
     * @param string             $view
     * @param string             $filename
     * @param array              $data
     */
    public function __construct(
        ContainerInterface $container,
        ViewManager $views,
        $namespace,
        $view,
        $filename,
        array $data = []
    );
}