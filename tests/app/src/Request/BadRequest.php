<?php

declare(strict_types=1);

namespace Spiral\App\Request;

use Spiral\Filters\Model\Filter;

class BadRequest extends Filter
{
    public const SCHEMA = [
        'name' => 'invalid:section.name'
    ];
}
