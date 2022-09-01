<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Fixtures;

use Spiral\Filters\Filter;
use Spiral\Filters\RenderWith;

#[RenderWith(TestErrorsInterfaceRenderer::class)]
final class FilterWithErrorsRenderer extends Filter
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
