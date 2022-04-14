<?php

namespace Spiral\Core\Internal;

final class State
{
    /**
     * @psalm-var array<non-empty-string, string|object|array{TResolver, bool}>
     */
    public array $bindings = [];

    /**
     * List of classes responsible for handling specific instance or interface. Provides ability to
     * delegate container functionality.
     */
    public array $injectors = [];
}
