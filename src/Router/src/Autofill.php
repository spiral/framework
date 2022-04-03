<?php

declare(strict_types=1);

namespace Spiral\Router;

final class Autofill implements \Stringable
{
    public function __construct(
        private readonly string $value
    ) {
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
