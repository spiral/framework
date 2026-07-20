<?php

declare(strict_types=1);

namespace Spiral\App\Request;

use Spiral\Filters\Model\Filter;

final class BadRequest extends Filter
{
    public const SCHEMA = [
        'name' => 'invalid:section.name',
    ];
}
