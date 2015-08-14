<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core;

/**
 * Service provides one of the application logic blocks, service must serve to controllers and
 * other logic. Service can declare itself as singleton by implementing SingletonInterface and
 * SINGLETON constant pointing to self.
 *
 * You can declare service logic in boot method, which is going to be executed using container.
 */
class Service extends Component
{
    /**
     * @invisible
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        if (method_exists($this, 'boot')) {
            $method = new \ReflectionMethod($this, 'boot');

            //Executing boot method
            call_user_func_array([$this, 'boot'], $container->resolveArguments($method));
        }
    }
}