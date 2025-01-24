<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Fixtures;

final class TestService extends AbstractTestService
{
    public int $counter = 0;

    public static function toUpperCase(string $value): string
    {
        return \strtoupper($value);
    }

    public function increment(): void
    {
        ++$this->counter;
    }

    protected function toLowerCase(string $value): string
    {
        return \strtolower($value);
    }
}
