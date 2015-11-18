<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core\Traits;

use Spiral\Core\Component;
use Spiral\Core\Exceptions\SingletonException;
use Spiral\Core\Exceptions\SugarException;
use Spiral\Core\InteropContainerInterface;

/**
 * Expects to be part of Component which has SINGLETON constant.
 */
trait SingletonTrait
{
    /**
     * Singletons will work as desired only under Spiral Container which can understand SINGLETON
     * constant. You can consider this functionality as "helper", if you can avoid using such
     * function - please do not use it.
     *
     * Global/static container used as fallback to receive class instance.
     *
     * @param InteropContainerInterface $container
     * @return static
     * @throws SugarException
     */
    public static function instance(InteropContainerInterface $container = null)
    {
        if (!is_subclass_of(static::class, Component::class)) {
            throw new SingletonException(
                "SingletonTrait has to be associated with Component classes only."
            );
        }

        //Excepted to be part of Component
        $container = !empty($container) ? $container : self::staticContainer();

        if (empty($container)) {
            throw new SugarException(
                "Singleton instance can be constructed only using valid Container."
            );
        }

        if (!defined('self::SINGLETON')) {
            throw new SingletonException("Singleton constant 'SINGLETON' is missing.");
        }

        return $container->get(static::SINGLETON);
    }
}