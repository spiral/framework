<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\Fixtures;

use Spiral\Tests\Prototype\Fixtures\InheritedInjection\InjectionOne;

class WithPromotedProperty extends AbstractPromotedProperty
{
    public function __construct(string $foo, private InjectionOne $one)
    {
        parent::__construct($foo);
    }
}
