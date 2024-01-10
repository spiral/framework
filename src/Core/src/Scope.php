<?php

declare(strict_types=1);

namespace Spiral\Core;

/**
 * DTO to define Container Scope.
 *
 * @psalm-import-type TResolver from BinderInterface
 *
 * @internal We are testing this feature, it may be changed in the future.
 */
final class Scope
{
    /**
     * @param null|string|\BackedEnum $name Scope name. Named scopes can have individual bindings and constrains.
     * @param array<non-empty-string, TResolver> $bindings Custom bindings for the new scope.
     * @param bool $autowire If {@see false}, closure will be invoked with just only the passed Container
     *        as the first argument. Otherwise, {@see InvokerInterface::invoke()} will be used to invoke the closure.
     */
    public function __construct(
        public readonly string|\BackedEnum|null $name = null,
        public readonly array $bindings = [],
        public readonly bool $autowire = true,
    ) {
    }
}
