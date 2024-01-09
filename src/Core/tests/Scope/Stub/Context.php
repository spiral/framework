<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Scope\Stub;

final class Context implements ContextInterface
{
    public function __construct(
        public \Stringable|string|null $value,
    ) {
    }

    public function getValue(): \Stringable|string|null
    {
        return $this->value;
    }
}
