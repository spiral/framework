<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Fixtures;

use Spiral\Core\InjectableConfig;

class IntKeysConfig extends InjectableConfig
{
    protected array $config = [
        1 => 'some',
        3 => 'other',
        7 => 'key'
    ];
}
