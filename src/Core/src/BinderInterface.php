<?php

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
     */
    public function bind(string $alias, string|array|callable|object $resolver): void;

    /**
     * Bind value resolver to container alias to be executed as cached. Resolver can be class name
     * (will be constructed only once), function array or Closure (executed only once call).
     *
     * @param non-empty-string|array{class-string, non-empty-string}|callable|object $resolver Can be result object or
     *        the same special callable value like the $target parameter in the {@see InvokerInterface::invoke()} method
     */
    public function bindSingleton(string $alias, string|array|callable|object $resolver): void;

    /**
     * Check if alias points to constructed instance (singleton).
     */
    public function hasInstance(string $alias): bool;

    public function removeBinding(string $alias): void;
}
