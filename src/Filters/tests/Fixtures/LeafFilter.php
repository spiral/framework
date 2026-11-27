<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Fixtures;

use Spiral\Filters\Attribute\Input\Post;
use Spiral\Filters\Model\Filter;

class LeafFilter extends Filter
{
    #[Post]
    public ?string $name = null;
}
