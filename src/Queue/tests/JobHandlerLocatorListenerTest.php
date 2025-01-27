<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue;

use Spiral\Attributes\ReaderInterface;
use Spiral\Core\Container;
use Spiral\Core\InvokerInterface;
use Spiral\Queue\Attribute\JobHandler as Attribute;
use Spiral\Queue\HandlerInterface;
use Spiral\Queue\HandlerRegistryInterface;
use Spiral\Queue\JobHandler;
use Spiral\Queue\JobHandlerLocatorListener;
use Spiral\Queue\QueueRegistry;

final class JobHandlerLocatorListenerTest extends TestCase
{
    public function testListen(): void
    {
        $handler = new class($this->createMock(InvokerInterface::class)) extends JobHandler {};

        $registry = new QueueRegistry(
            new Container(),
            new Container(),
            $this->createMock(HandlerRegistryInterface::class),
        );

        $reader = $this->createMock(ReaderInterface::class);
        $reader
            ->expects($this->once())
            ->method('firstClassMetadata')
            ->willReturn(new Attribute('test'));

        $listener = new JobHandlerLocatorListener($reader, $registry);
        $listener->listen(new \ReflectionClass($handler::class));

        self::assertInstanceOf(HandlerInterface::class, $registry->getHandler('test'));
    }
}
