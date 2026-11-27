<?php

declare(strict_types=1);

namespace Spiral\Tests\Bench\Filters\Fixture;

use Spiral\Filters\Attribute\Input\Post;
use Spiral\Filters\Attribute\NestedArray;

final class WideNodeFilter extends WideLeafFilter
{
    #[NestedArray(class: WideNodeFilter::class, input: new Post('children'))]
    public array $children = [];
}
