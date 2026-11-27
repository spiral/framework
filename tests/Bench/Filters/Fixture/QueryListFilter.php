<?php

declare(strict_types=1);

namespace Spiral\Tests\Bench\Filters\Fixture;

use Spiral\Filters\Attribute\Input\Query;
use Spiral\Filters\Attribute\NestedArray;
use Spiral\Filters\Model\Filter;

final class QueryListFilter extends Filter
{
    #[NestedArray(class: QueryLeafFilter::class, input: new Query('items'))]
    public array $items = [];
}
