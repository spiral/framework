<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\Fixtures;

use Spiral\Prototype\Traits\PrototypeTrait;

final class WithPromotedProperty extends WithConstructor
{
    use PrototypeTrait;

    public function __construct(
        string $foo,
        private readonly HydratedClass $hydrated
    ) {
        parent::__construct($this->hydrated);
    }

    public function some(): TestClass
    {
        return $this->hydrated->getTestClass();
    }

    public function other(): void
    {
        $this->emptyClass->method();
    }
}
