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

class ParentDeepPathFilter extends Filter
{
    public const SCHEMA = [
        'name' => 'name',
        'test' => [TestFilter::class, 'custom.test']
    ];
}
