<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Attribute\Stub;

use Spiral\Queue\QueueableInterface;

final class QueueableWithInterfaceAndStaticQueue implements QueueableInterface
{
    public static function getQueue(): string
    {
        return 'test';
    }
}
