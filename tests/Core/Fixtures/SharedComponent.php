<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Tests\Core\Fixtures;

use Interop\Container\ContainerInterface;
use Spiral\Core\Component;

class SharedComponent extends Component
{
    public function getContainer()
    {
        return $this->iocContainer();
    }

    /**
     * @param ContainerInterface $container
     *
     * @return ContainerInterface|null
     */
    public static function shareContainer(ContainerInterface $container = null)
    {
        return self::staticContainer($container);
    }
}