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

abstract class AbstractFilter extends Filter
{
    public const SCHEMA = [
        'id' => 'query:id'
    ];

    public const VALIDATES = [
        'id' => ['notEmpty']
    ];
}
