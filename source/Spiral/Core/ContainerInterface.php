<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Core;

use Interop\Container\ContainerInterface as InteropContainer;

/**
 * Spiral IoC container interface. Used to resolve dependencies and etc.
 *
 * @see  InjectorInterface
 * @see  SingletonInterface
 *
 * Factory, Recolver and Scoper interfaces MIGHT be removed from extends in a later versions.
 */
interface ContainerInterface extends
    FactoryInterface,
    ResolverInterface,
    InteropContainer,
    ScoperInterface
{
    /**
     * Bind value resolver to container alias. Resolver can be class name (will be constructed
     * every method call), function array or Closure (executed every call). Only object resolvers
     * supported by this method.
     *
     * @param string                $alias
     * @param string|array|callable $resolver
     */
    public function bind(string $alias, $resolver);

    /**
     * Bind value resolver to container alias to be executed as cached. Resolver can be class name
     * (will be constructed only once), function array or Closure (executed only once call).
     *
     * @param string                $alias
     * @param string|array|callable $resolver
     */
    public function bindSingleton(string $alias, $resolver);

    /**
     * Check if alias points to constructed instance (singleton).
     *
     * @param string $alias
     *
     * @return bool
     */
    public function hasInstance(string $alias);

    /**
     * @param string $alias
     */
    public function removeBinding(string $alias);
}
