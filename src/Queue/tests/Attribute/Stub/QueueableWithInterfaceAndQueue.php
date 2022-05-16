<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Attribute\Stub;

use Spiral\Queue\QueueableInterface;

final class QueueableWithInterfaceAndQueue implements QueueableInterface
{
    public function getQueue(): string
    {
        return 'test';
    }
}
