<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Fixtures;

use Spiral\Filters\Attribute\Input\Post;
use Spiral\Filters\Attribute\NestedArray;
use Spiral\Filters\Model\Filter;

class BranchFilter extends Filter
{
    #[NestedArray(class: LeafFilter::class, input: new Post('leaves'))]
    public array $leaves = [];
}
