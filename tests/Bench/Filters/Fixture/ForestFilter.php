<?php

declare(strict_types=1);

namespace Spiral\Tests\Bench\Filters\Fixture;

use Spiral\Filters\Attribute\Input\Post;
use Spiral\Filters\Attribute\NestedArray;
use Spiral\Filters\Model\Filter;
use Spiral\Tests\Filters\Fixtures\TreeFilter;

final class ForestFilter extends Filter
{
    #[NestedArray(class: TreeFilter::class, input: new Post('trees'))]
    public array $trees = [];
}
