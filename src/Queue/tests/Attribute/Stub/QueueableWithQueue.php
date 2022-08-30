<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Attribute\Stub;

use Spiral\Queue\Attribute\Queueable;

/**
 * @Queueable(queue="test")
 */
final class QueueableWithQueue
{
}
