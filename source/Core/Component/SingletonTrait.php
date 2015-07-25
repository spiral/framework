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

trait SingletonTrait
{
    /**
     * Return singleton component instance, such method can be used only if "SINGLETON" constant
     * declared and not empty. Class will be automatically created using IoC. Another class instance
     * or realization can be binded under componentAlias, in this case this method return that object.
     *
     * @param Container $container Container instance to resolve object, global container will be used
     *                             if no option provided.
     * @return $this
     */
    public static function getInstance(Container $container = null)
    {
        if (empty($container))
        {
            $container = Container::getInstance();
        }

        return $container->get(static::SINGLETON);
    }
}