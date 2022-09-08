<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\Fixtures;

class HydratedClass
{
    private $testClass;

    public function __construct(TestClass $t)
    {
        $this->testClass = $t;
    }

    public function getTestClass(): TestClass
    {
        return $this->testClass;
    }
}
