<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\Fixtures\InheritedInjection;

use Spiral\Prototype\Traits\PrototypeTrait;

class ChildClass extends MiddleClass
{
    use PrototypeTrait;

    public function useTwo(): void
    {
        $this->two;
    }
}
