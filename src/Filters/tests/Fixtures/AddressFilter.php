<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Fixtures;

use Spiral\Filters\Attribute\Input\Post;
use Spiral\Filters\Model\Filter;

class AddressFilter extends Filter
{
    #[Post]
    public string $city;

    #[Post]
    public string $address;
}
