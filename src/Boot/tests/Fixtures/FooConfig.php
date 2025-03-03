<?php

declare(strict_types=1);

namespace Spiral\Tests\Boot\Fixtures;

use Spiral\Core\InjectableConfig;

class FooConfig extends InjectableConfig
{
    public const CONFIG = 'foo';
}
