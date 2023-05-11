<?php

declare(strict_types=1);

namespace Spiral\Core\Config;

/**
 * Static permanent object
 */
final class Shared extends Binding
{
    public function __construct(
        public readonly object $value,
    ) {
    }

    public function __toString(): string
    {
        return 'Shared object of class ' . $this->value::class;
    }
}
