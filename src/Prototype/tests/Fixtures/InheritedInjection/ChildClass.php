<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\Fixtures\InheritedInjection;

final class ChildClass extends MiddleClass
{
    /**
     * @codeCoverageIgnore
     */
    public function useTwo(): void
    {
        $this->two;
    }
}
