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
        public readonly bool $singleton = false,
    ) {}

    public function __toString(): string
    {
        return 'Shared object of class ' . $this->value::class;
    }

    /**
     * @return class-string
     * @internal
     */
    public function getReturnClass(): string
    {
        return $this->value::class;
    }
}
