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

class MessageFilter extends Filter
{
    public const SCHEMA = [
        'id' => 'query:id'
    ];

    public const VALIDATES = [
        'id' => [
            ['notEmpty', 'err' => '[[ID is not valid.]]']
        ]
    ];
}
