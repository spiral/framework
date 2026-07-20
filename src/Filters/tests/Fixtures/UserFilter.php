<?php

declare(strict_types=1);

namespace Spiral\Tests\Filters\Fixtures;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Spiral\Filters\Attribute\Input\Post;
use Spiral\Filters\Attribute\Setter;
use Spiral\Filters\Model\Filter;

class UserFilter extends Filter
{
    #[Post]
    public string $name;

    #[Post]
    public Status $status;

    #[Post]
    #[Setter([Status::class, 'from'])]
    public Status $activationStatus;

    #[Post]
    public UuidInterface $groupUuid;

    #[Post]
    #[Setter([Uuid::class, 'fromString'])]
    public UuidInterface $friendUuid;
}
