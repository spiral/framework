<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Fixtures;

final class ClassWithUndefinedDependency
{
    public function __construct(InvalidClass $class)
    {
    }
}
