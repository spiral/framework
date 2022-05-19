<?php

declare(strict_types=1);

namespace Spiral\Boot\Injector;

use Spiral\Core\Container\InjectableInterface;

interface InjectableEnumInterface extends InjectableInterface
{
    public const INJECTOR = EnumInjector::class;
}
