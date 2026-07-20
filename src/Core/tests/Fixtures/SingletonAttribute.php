<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Fixtures;

use Spiral\Core\Attribute\Singleton;

#[Singleton]
final class SingletonAttribute
{
    public function nullableScalar(?string $nullable): ?string
    {
        return $nullable;
    }
}
