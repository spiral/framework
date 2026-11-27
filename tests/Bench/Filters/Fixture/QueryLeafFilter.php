<?php

declare(strict_types=1);

namespace Spiral\Tests\Bench\Filters\Fixture;

use Spiral\Filters\Attribute\Input\Query;
use Spiral\Filters\Model\Filter;

final class QueryLeafFilter extends Filter
{
    #[Query]
    public ?string $name = null;
}
