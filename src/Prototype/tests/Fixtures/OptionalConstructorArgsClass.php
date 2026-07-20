<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\Fixtures;

class OptionalConstructorArgsClass
{
    public function __construct(string $a, ?string $b, ?string $c = 'c', ?string $d = null, string $e = 'e')
    {
    }

    public function getTestClass(): TestClass
    {
        return $this->testClass;
    }
}
