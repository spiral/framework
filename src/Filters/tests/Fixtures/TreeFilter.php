<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Fixtures;

use Spiral\Filters\Attribute\Input\Post;
use Spiral\Filters\Attribute\NestedArray;
use Spiral\Filters\Model\Filter;

class TreeFilter extends Filter
{
    #[NestedArray(class: BranchFilter::class, input: new Post('branches'))]
    public array $branches = [];
}
