<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
