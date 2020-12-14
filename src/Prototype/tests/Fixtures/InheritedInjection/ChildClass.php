<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\Fixtures\InheritedInjection;

class ChildClass extends ParentClass
{
    public function useTwo(): void
    {
        $this->two;
    }
}
