<?php

declare(strict_types=1);

namespace Spiral\Tests\Prototype\Fixtures;

use Spiral\Prototype\Dependency;

class Dependencies
{
    public static function convert(array $deps): array
    {
        $converted = [];
        foreach ($deps as $name => $type) {
            $converted[$name] = Dependency::create($name, $type);
        }

        return $converted;
    }
}
