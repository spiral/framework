<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Core;

/**
 * Manages container bindings.
 */
interface BinderInterface
{
    /**
     * Bind value resolver to container alias. Resolver can be class name (will be constructed
     * every method call), function array or Closure (executed every call). Only object resolvers
     * supported by this method.
     *
     * @param string|array|callable $resolver
     */
    public function bind(string $alias, $resolver): void;

    /**
     * Bind value resolver to container alias to be executed as cached. Resolver can be class name
     * (will be constructed only once), function array or Closure (executed only once call).
     *
     * @param string|array|callable $resolver
     */
    public function bindSingleton(string $alias, $resolver): void;

    /**
     * Check if alias points to constructed instance (singleton).
     */
    public function hasInstance(string $alias): bool;

    public function removeBinding(string $alias): void;
}
