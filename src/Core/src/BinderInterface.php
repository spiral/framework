<?php

declare(strict_types=1);

namespace Spiral\Core;

use Spiral\Core\Container\InjectorInterface;

/**
 * Manages container bindings.
 *
 * @psalm-type TResolver = class-string|non-empty-string|object|callable|array{class-string, non-empty-string}
 */
interface BinderInterface
{
    /**
     * Bind value resolver to container alias. Resolver can be class name (will be constructed
     * every method call), function array or Closure (executed every call). Only object resolvers
     * supported by this method.
     *
     * @psalm-param TResolver $resolver
     */
    public function bind(string $alias, string|array|callable|object $resolver): void;

    /**
     * Bind value resolver to container alias to be executed as cached. Resolver can be class name
     * (will be constructed only once), function array or Closure (executed only once call).
     *
     * @param TResolver $resolver Can be result object or
     *        the same special callable value like the $target parameter in the {@see InvokerInterface::invoke()} method
     */
    public function bindSingleton(string $alias, string|array|callable|object $resolver): void;

    /**
     * Check if alias points to constructed instance (singleton).
     */
    public function hasInstance(string $alias): bool;

    public function removeBinding(string $alias): void;

    /**
     * Bind class or class interface to the injector source (InjectorInterface).
     *
     * @template TClass
     *
     * @param class-string<TClass> $class
     * @param class-string<InjectorInterface<TClass>> $injector
     */
    public function bindInjector(string $class, string $injector): void;

    public function removeInjector(string $class): void;

    /**
     * Check if class points to injector.
     */
    public function hasInjector(string $class): bool;
}
