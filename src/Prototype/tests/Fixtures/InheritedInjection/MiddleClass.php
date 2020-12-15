<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\Fixtures\InheritedInjection;

class MiddleClass extends ParentClass
{
    // @codeCoverageIgnoreStart
    public function __construct(\stdClass $ownInjection)
    {
    }
}
