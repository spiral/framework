<?php

declare(strict_types=1);

namespace Spiral\Tests\Bench\Filters\Fixture;

use Spiral\Filters\Attribute\Input\Post;
use Spiral\Filters\Attribute\NestedArray;
use Spiral\Filters\Model\Filter;

final class WrapListFilter extends Filter
{
    #[NestedArray(class: WrapFilter::class, input: new Post('wraps'))]
    public array $wraps = [];
}
