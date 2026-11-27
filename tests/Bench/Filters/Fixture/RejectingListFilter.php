<?php

declare(strict_types=1);

namespace Spiral\Tests\Bench\Filters\Fixture;

use Spiral\Filters\Attribute\Input\Post;
use Spiral\Filters\Attribute\NestedArray;
use Spiral\Filters\Model\Filter;

final class RejectingListFilter extends Filter
{
    #[NestedArray(class: RejectingLeafFilter::class, input: new Post('items'))]
    public array $items = [];
}
