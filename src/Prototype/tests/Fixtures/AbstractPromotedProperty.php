<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\Fixtures;

abstract class AbstractPromotedProperty
{
    public function __construct(protected string $foo)
    {
    }
}
