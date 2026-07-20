<?php

declare(strict_types=1);

namespace Spiral\Tests\Core\Fixtures;

class UnionTypes
{
    public static function example(SampleClass|TypedClass $example)
    {
    }

    public static function unionNull(null|string $nullable): null|string
    {
        return $nullable;
    }
}
