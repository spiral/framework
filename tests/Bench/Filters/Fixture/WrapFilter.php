<?php

declare(strict_types=1);

namespace Spiral\Tests\Bench\Filters\Fixture;

use Spiral\Filters\Attribute\NestedFilter;
use Spiral\Filters\Model\Filter;

final class WrapFilter extends Filter
{
    #[NestedFilter(class: FlatFilter::class)]
    public FlatFilter $flat;
}
