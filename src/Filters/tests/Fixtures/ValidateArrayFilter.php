<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Filters\Fixtures;

use Spiral\Filters\Filter;

class ValidateArrayFilter extends Filter
{
    public const SCHEMA = [
        'tests' => [TestFilter::class]
    ];

    public const VALIDATES = [
        'tests' => [
            ['notEmpty', 'err' => '[[No tests are specified.]]']
        ]
    ];
}
