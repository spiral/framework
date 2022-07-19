<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\App\Request;

use Spiral\Filters\Dto\Filter;

class BadRequest extends Filter
{
    public const SCHEMA = [
        'name' => 'invalid:section.name'
    ];
}
