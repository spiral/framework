<?php

declare(strict_types=1);

namespace Spiral\Tests\Bench\Filters\Fixture;

use Spiral\Filters\Attribute\Input\Post;
use Spiral\Filters\Attribute\Setter;
use Spiral\Filters\Model\Filter;

final class RejectingLeafFilter extends Filter
{
    #[Post]
    #[Setter(filter: [self::class, 'reject'])]
    public mixed $name = null;

    public static function reject(mixed $value): never
    {
        throw new \InvalidArgumentException('The value is rejected.');
    }
}
