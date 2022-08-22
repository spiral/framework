<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Fixtures;

use Spiral\Filters\Attribute\Input\Post;
use Spiral\Filters\Attribute\NestedFilter;
use Spiral\Filters\Model\Filter;

class SomeFilter extends Filter
{
    #[Post]
    public string $name;

    #[NestedFilter(class: AddressFilter::class)]
    public AddressFilter $address;
}
