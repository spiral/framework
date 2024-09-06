<?php

declare(strict_types=1);

namespace Spiral\Tests\Config;

class Value
{
    public function __construct(private readonly string $value = 'value!')
    {
    }

    public function getValue()
    {
        return $this->value;
    }
}
