<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Fixtures;

use Ramsey\Uuid\UuidInterface;
use Spiral\Filters\Attribute\Input\Post;
use Spiral\Filters\Model\Filter;

class UserFilter extends Filter
{
    #[Post]
    public string $name;

    #[Post]
    public Status $status;

    #[Post]
    public UuidInterface $groupUuid;
}
