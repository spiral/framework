<?php

namespace Spiral\Tests\Router\Fixtures;

class InArrayPattern implements \Stringable
{
    public function __construct(
        private readonly array $values
    ) {
    }

    public function __toString()
    {
        return \sprintf('(%s)', \implode('|', $this->values));
    }
}
