<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue;

use Spiral\Queue\Attribute\Queueable as QueueableAttribute;
use Spiral\Queue\Attribute\QueueableTrait;
use Spiral\Tests\Queue\Attribute\Stub\NotQueueable;
use Spiral\Tests\Queue\Attribute\Stub\Queueable;
use Spiral\Tests\Queue\Attribute\Stub\QueueableWithInterface;
use Spiral\Tests\Queue\Attribute\Stub\QueueableWithInterfaceAndQueue;
use Spiral\Tests\Queue\Attribute\Stub\QueueableWithInterfaceAndStaticQueue;
use Spiral\Tests\Queue\Attribute\Stub\QueueableWithQueue;

final class QueueableTraitTest extends TestCase
{
    use QueueableTrait;

    public function testQueueableClass(): void
    {
        $queueable = $this->findQueueable(Queueable::class);

        $this->assertInstanceOf(QueueableAttribute::class, $queueable);
        $this->assertNull($queueable->queue);
    }

    public function testQueueableClassWithQueue(): void
    {
        $queueable = $this->findQueueable(QueueableWithQueue::class);

        $this->assertInstanceOf(QueueableAttribute::class, $queueable);
        $this->assertSame('test', $queueable->queue);
    }

    public function testQueueableImplementedInterface(): void
    {
        $queueable = $this->findQueueable(QueueableWithInterface::class);

        $this->assertInstanceOf(QueueableAttribute::class, $queueable);
        $this->assertNull($queueable->queue);
    }

    public function testQueueableImplementedInterfaceAndQueue(): void
    {
        $queueable = $this->findQueueable(new QueueableWithInterfaceAndQueue());

        $this->assertInstanceOf(QueueableAttribute::class, $queueable);
        $this->assertSame('test', $queueable->queue);
    }

    public function testQueueableImplementedInterfaceAndStaticQueue(): void
    {
        $queueable = $this->findQueueable(new QueueableWithInterfaceAndStaticQueue());

        $this->assertInstanceOf(QueueableAttribute::class, $queueable);
        $this->assertSame('test', $queueable->queue);
    }

    public function testNotQueueableClass(): void
    {
        $queueable = $this->findQueueable(NotQueueable::class);

        $this->assertNull($queueable);
    }
}
