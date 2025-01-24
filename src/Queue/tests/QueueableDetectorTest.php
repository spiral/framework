<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue;

use PHPUnit\Framework\Attributes\DataProvider;
use Spiral\Attributes\Factory;
use Spiral\Queue\QueueableDetector;
use Spiral\Tests\Queue\Attribute\Stub\NotQueueable;
use Spiral\Tests\Queue\Attribute\Stub\NotQueueableInterface;
use Spiral\Tests\Queue\Attribute\Stub\Queueable;
use Spiral\Tests\Queue\Attribute\Stub\QueueableWithInterface;
use Spiral\Tests\Queue\Attribute\Stub\QueueableWithInterfaceAndQueue;
use Spiral\Tests\Queue\Attribute\Stub\QueueableWithInterfaceAndStaticQueue;
use Spiral\Tests\Queue\Attribute\Stub\QueueableWithQueue;

final class QueueableDetectorTest extends TestCase
{
    public static function queueableProvider(): \Traversable
    {
        yield [Queueable::class, true, null];
        yield [QueueableWithQueue::class, true, 'test'];
        yield [QueueableWithInterface::class, true, null];
        yield [new QueueableWithInterfaceAndQueue(), true, 'test'];
        yield [new QueueableWithInterfaceAndStaticQueue(), true, 'test'];
        yield [NotQueueable::class, false, null];
        yield [new NotQueueable(), false, null];
        yield [NotQueueableInterface::class, false, null];
    }

    #[DataProvider('queueableProvider')]
    public function testQueueable($object, bool $queueable, ?string $queue): void
    {
        $detector = new QueueableDetector((new Factory())->create());

        self::assertSame($queueable, $detector->isQueueable($object));
        self::assertSame($queue, $detector->getQueue($object));
    }
}
