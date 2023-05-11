<?php

declare(strict_types=1);

namespace Spiral\Core\Config;

/**
 * Factory that can be resolved later.
 */
final class DeferredFactory extends Binding
{
    /**
     * @param array{0: object|non-empty-string, 1: non-empty-string, ...} $factory
     */
    public function __construct(
        public readonly array $factory,
        public readonly bool $singleton = false,
    ) {
    }

    public function __toString(): string
    {
        return sprintf(
            "Deferred factory '%s'->%s()",
            \is_string($this->factory[0]) ? $this->factory[0] : \get_debug_type($this->factory[0]),
            $this->factory[1],
        );
    }
}
