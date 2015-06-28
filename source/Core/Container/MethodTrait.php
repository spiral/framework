<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core\Container;

use Spiral\Core\Container;

trait MethodTrait
{
    /**
     * Helper method used to call object function with automatic parameters resolution using Container.
     *
     * @param string|\Closure $method     Method name.
     * @param array           $parameters Set of parameters to populate.
     * @param Container       $container  Container to resolve dependencies.
     * @return mixed
     */
    protected function callFunction(
        $method = '',
        array $parameters = [],
        Container $container = null
    )
    {
        $reflection = new \ReflectionMethod($this, $method);
        $reflection->setAccessible(true);

        if (empty($container))
        {
            $container = Container::getInstance();
        }

        return $reflection->invokeArgs(
            $this,
            $container->resolveArguments($reflection, $parameters)
        );
    }
}
