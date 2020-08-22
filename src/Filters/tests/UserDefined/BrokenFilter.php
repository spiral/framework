<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Filters\UserDefined;

use Spiral\Filters\Filter;

class BrokenFilter extends Filter
{
    public const SCHEMA = [
        'id' => []
    ];
}
