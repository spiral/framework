<?php

declare(strict_types=1);

namespace Spiral\Tests\Interceptors\Unit\Stub;

abstract class AbstractTestService
{
    public function parentMethod(string $value): string
    {
        return \strtolower($value);
    }
}
