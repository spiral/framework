<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Fixtures;

final class PrivateConstructor
{
    private function __construct() {}

    private static function privateMethod(int $result): int
    {
        return $result;
    }

    public static function publicMethod(int $result): int
    {
        return $result;
    }
}
