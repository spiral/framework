<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\Fixtures;

use Spiral\Prototype\Traits\PrototypeTrait;

class TestEmptyClass
{
    use PrototypeTrait;

    public function getTest(): void
    {
    }

    public function method(): void
    {
    }
}
