<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core\Component;

use Spiral\Core\Container;
use Spiral\Core\CoreException;

trait MakeTrait
{
    /**
     * Create or retrieve component instance using IoC container. This method can return already
     * existed instance of class if that instance were defined as singleton and binded in core under
     * same class name. Using binding mechanism target instance can be redefined to use another
     * declaration. Be aware of that.
     *
     * @param array     $parameters Named parameters list to use for instance constructing.
     * @param Container $container  Container instance used to resolve dependencies, if not provided
     *                              global container will be used.
     * @return $this
     * @throws CoreException
     */
    public static function make($parameters = [], Container $container = null)
    {
        if (empty($container))
        {
            $container = Container::getInstance();
        }

        return $container->get(get_called_class(), $parameters);
    }
}