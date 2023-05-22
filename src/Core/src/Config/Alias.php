<?php

declare(strict_types=1);

namespace Spiral\Core\Config;

/**
 * Links to another binding.
 */
final class Alias extends Binding
{
    public function __construct(
        public readonly string $alias,
        public readonly bool $singleton = false,
    ) {
    }

    public function __toString(): string
    {
        return sprintf('Alias to `%s`', $this->alias);
    }
}
