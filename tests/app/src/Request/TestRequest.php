<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\App\Request;

use Spiral\Filters\Attribute\Input\Data;
use Spiral\Filters\Filter;

class TestRequest extends Filter
{
    #[Data(key: 'name')]
    public ?string $name = null;

    #[Data(key: 'section.value')]
    public ?string $sectionValue = null;

    // TODO: add tests for validation
//    public const VALIDATES = [
//        'name' => ['notEmpty', 'string']
//    ];
}
