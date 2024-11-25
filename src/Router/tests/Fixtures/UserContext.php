<?php

declare(strict_types=1);

namespace Spiral\Tests\Router\Fixtures;

final class UserContext
{
    private function __construct() {}

    public static function create(): self
    {
        return new self();
    }
}
