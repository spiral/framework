<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Fixtures;

abstract class AbstractTestService
{
    public function parentMethod(string $value): string
    {
        return \strtolower($value);
    }
}
