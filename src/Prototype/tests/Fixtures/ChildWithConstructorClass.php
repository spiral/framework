<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\Fixtures;

use Spiral\Prototype\Traits\PrototypeTrait;

class ChildWithConstructorClass extends WithConstructor
{
    use PrototypeTrait;

    public function __construct()
    {
    }

    public function testMe()
    {
        return $this->testClass;
    }

    public function method(): void
    {
        $test2 = $this->test2;
        $test3 = $this->test3;
        $test = $this->test;
    }
}
