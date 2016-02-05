<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
namespace Spiral\Core;

use Interop\Container\ContainerInterface as InteropContainer;
use Spiral\Core\Exceptions\Container\ContainerException;

/**
 * Spiral IoC container interface. Used to resolve dependencies and etc.
 *
 * @todo different name?
 *
 * @see InjectorInterface
 * @see SingletonInterface
 */
interface ContainerInterface extends FactoryInterface, ResolverInterface, InteropContainer
{
    /**
     * Bind value resolver to container alias. Resolver can be class name (will be constructed
     * every method call), function array or Closure (executed every call). Only object resolvers
     * supported by this method.
     *
     * @param string                $alias
     * @param string|array|callable $resolver
     */
    public function bind($alias, $resolver);

    /**
     * Bind value resolver to container alias to be executed as cached. Resolver can be class name
     * (will be constructed only once), function array or Closure (executed only once call).
     *
     * @param string                $alias
     * @param string|array|callable $resolver
     */
    public function bindSingleton($alias, $resolver);

    /**
     * Replace existed binding and return payload (implementation specific data) of previous
     * binding, previous binding can be restored using restore() method and such payload.
     *
     * @see restore()
     * @param string                $alias
     * @param string|array|callable $resolver
     * @return mixed
     */
    public function replace($alias, $resolver);

    /**
     * Restore previously pulled binding value using implementation specific payload. Method should
     * only accept result of replace() method.
     *
     * @see replace
     * @param mixed $replacePayload
     * @throws ContainerException
     */
    public function restore($replacePayload);

    /**
     * Check if alias points to constructed instance (singleton).
     *
     * @param string $alias
     * @return bool
     */
    public function hasInstance($alias);

    /**
     * @param string $alias
     */
    public function removeBinding($alias);
}
