<?php

declare(strict_types=1);

namespace Spiral\Tests\Bench\Filters\Fixture;

use Spiral\Filters\Attribute\Input\Post;
use Spiral\Filters\Attribute\NestedArray;
use Spiral\Filters\Model\Filter;
use Spiral\Tests\Filters\Fixtures\LeafFilter;

class FlatFilter extends Filter
{
    #[NestedArray(class: LeafFilter::class, input: new Post('items'))]
    public array $items = [];
}
