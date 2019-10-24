<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\App\Request;

use Spiral\Filters\Filter;

class TestRequest extends Filter
{
    public const SCHEMA = [
        'name'  => 'data:name',
        'value' => 'data:section.value'
    ];

    public const VALIDATES = [
        'name' => ['notEmpty', 'string']
    ];
}
