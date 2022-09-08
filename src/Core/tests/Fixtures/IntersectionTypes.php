<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Fixtures;

use Countable;
use IteratorAggregate;

class IntersectionTypes
{
    public static function example(Countable&IteratorAggregate $example)
    {
    }
}
