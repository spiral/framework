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

class BadRequest extends Filter
{
    const SCHEMA = [
        'name' => 'invalid:section.name'
    ];
}
