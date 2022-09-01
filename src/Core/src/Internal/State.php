<?php

namespace Spiral\Core\Internal;

/**
 * @psalm-type TResolver = class-string|non-empty-string|callable|array{class-string, non-empty-string}
 *
 * @internal
 */
final class State
{
    /**
     * @var array<non-empty-string, string|object|array{TResolver, bool}>
     */
    public array $bindings = [];

    /**
     * List of classes responsible for handling specific instance or interface. Provides ability to
     * delegate container functionality.
     */
    public array $injectors = [];

    public function destruct(): void
    {
        $this->injectors = [];
        $this->bindings = [];
    }
}
