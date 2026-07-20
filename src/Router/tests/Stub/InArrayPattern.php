<?php

declare(strict_types=1);

namespace Spiral\Tests\Router\Stub;

final class InArrayPattern implements \Stringable
{
    public function __construct(
        private readonly array $values,
    ) {}

    public function __toString()
    {
        return \sprintf('(%s)', \implode('|', $this->values));
    }
}
