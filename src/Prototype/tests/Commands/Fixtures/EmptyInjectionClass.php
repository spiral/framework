<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\Commands\Fixtures;

use Spiral\Prototype\Traits\PrototypeTrait;

class EmptyInjectionClass
{
    use PrototypeTrait;

    public function do($var): void
    {
    }
}
