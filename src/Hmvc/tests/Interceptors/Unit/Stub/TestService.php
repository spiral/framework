<?php

declare(strict_types=1);

namespace Spiral\Tests\Interceptors\Unit\Stub;

final class TestService
{
    public int $counter = 0;

    public function increment(): void
    {
        ++$this->counter;
    }
}
