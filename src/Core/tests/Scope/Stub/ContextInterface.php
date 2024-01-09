<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Scope\Stub;

interface ContextInterface
{
    public function getValue(): \Stringable|string|null;
}
