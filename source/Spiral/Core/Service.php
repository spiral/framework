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
 * Service provides one of the application constructing blocks, service must serve to controllers
 * and other logic. Service can declare itself as singleton by implementing SingletonInterface and
 * SINGLETON constant pointing to self.
 *
 * Count service as layer (model) between data entities and various controllers.
 *
 * You can declare service boot logic and dependencies in init method which is going to be
 * executed using container. In addition service can access components bindings using string alias.
 */
class Service extends Component
{
    use SharedTrait;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}