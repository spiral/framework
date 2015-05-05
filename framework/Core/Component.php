<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 * @copyright ©2009-2015
 */
namespace Spiral\Core;

abstract class Component
{
    /**
     * Declares to IoC that component instance should be treated as singleton.
     */
    const SINGLETON = '';

    /**
     * Component alias name should be used for logger chanel and other operations.
     *
     * @return string
     */
    public static function getAlias()
    {
        return get_called_class();
    }

    /**
     * Create or retrieve component instance using IoC container. This method can return already
     * existed instance of class if that instance were defined as singleton and binded in core under
     * same class name. Using binding mechanism target instance can be redefined to use another
     * declaration. Be aware of that.
     *
     * @param array $parameters Named parameters list to use for instance constructing.
     * @return static
     * @throws CoreException
     */
    public static function make($parameters = array())
    {
        return Container::getInstance()->get(
            get_called_class(),
            $parameters
        );
    }
}