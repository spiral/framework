<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core;

use Spiral\Core\Traits\SharedTrait;

/**
 * Generic spiral service only provide simplified access to shared components and instances.
 */
class Service extends Component
{
    /**
     * Access to shared components and entities.
     */
    use SharedTrait;

    /**
     * @param InteropContainerInterface $container
     */
    public function __construct(InteropContainerInterface $container)
    {
        $this->container = $container;
    }
}