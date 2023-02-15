<?php

declare(strict_types=1);

namespace Spiral\Tests\Console;

use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Console\Event\CommandFinished;
use Spiral\Console\Event\CommandStarting;
use Spiral\Core\Event\InterceptorCalling;
use Spiral\Tests\Console\Fixtures\TestCommand;

final class EventsTest extends BaseTest
{
    public function testEventsShouldBeDispatched(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        $dispatcher->expects(self::exactly(3))
            ->method('dispatch')
            ->withConsecutive(
                [
                    $this->callback(static fn(mixed $event): bool =>
                        $event instanceof CommandStarting && $event->command instanceof TestCommand
                    )
                ],
                [
                    $this->callback(static fn(mixed $event): bool => $event instanceof InterceptorCalling)
                ],
                [
                    $this->callback(static fn(mixed $event): bool =>
                        $event instanceof CommandFinished && $event->command instanceof TestCommand
                    )
                ],
            );

        $core = $this->getCore(
            locator: $this->getStaticLocator([new TestCommand()]),
            eventDispatcher: $dispatcher
        );

        $core->run('test');
    }
}
