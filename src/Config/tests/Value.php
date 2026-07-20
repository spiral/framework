<?php

declare(strict_types=1);

namespace Spiral\Tests\Config;

final class Value
{
    public function __construct(private readonly string $value = 'value!') {}

    public function getValue(): string
    {
        return $this->value;
    }
}
