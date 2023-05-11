<?php

declare(strict_types=1);

namespace Spiral\Core;

use Psr\Container\ContainerInterface;

/**
 * Provides ability to run code withing isolated IoC scope.
 *
 * @psalm-import-type TResolver from BinderInterface
 *
 * @internal We are testing this feature, it may be changed in the future.
 */
interface ContainerScopeInterface extends ContainerInterface
{
    /**
     * Make a Binder proxy to configure bindings for a specific scope.
     *
     * @param null|string $scope Scope name.
     *        If {@see null}, binder for the current working scope will be returned.
     *        If {@see string}, the default binder for the given scope will be returned. Default bindings won't affect
     *        already created Container instances except the case with the root one.
     */
    public function getBinder(?string $scope = null): BinderInterface;

    /**
     * Get current scope container.
     *
     * @internal it might be removed in the future.
     */
    public function getCurrentContainer(): ContainerInterface;

    /**
     * Invoke given closure or function withing specific IoC scope.
     *
     * @template TReturn
     *
     * @param callable(mixed ...$params): TReturn $closure
     * @param array<non-empty-string, TResolver> $bindings Custom bindings for the new scope.
     * @param null|string $name Scope name. Named scopes can have individual bindings and constrains.
     * @param bool $autowire If {@see false}, closure will be invoked with just only the passed Container as an
     *        argument. Otherwise, {@see InvokerInterface::invoke()} will be used to invoke the closure.
     *
     * @return TReturn
     * @throws \Throwable
     */
    public function runScoped(callable $closure, array $bindings = [], ?string $name = null, bool $autowire = true): mixed;
}
