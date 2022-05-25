<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\Fixtures;

use Spiral\Boot\Injector\InjectableEnumInterface;
use Spiral\Boot\Injector\ProvideFrom;

#[ProvideFrom(method: 'detect')]
enum InjectableEnum implements InjectableEnumInterface
{
    case Foo;
    case Bar;

    public static function detect(): self
    {
        return self::Bar;
    }
}
