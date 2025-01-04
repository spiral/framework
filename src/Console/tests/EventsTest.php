<?php

declare(strict_types=1);

namespace Spiral\Tests\Console;

use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Console\Event\CommandFinished;
use Spiral\Console\Event\CommandStarting;
use Spiral\Core\Event\InterceptorCalling;
use Spiral\Tests\Console\Fixtures\TestCommand;

final class EventsTest extends BaseTestCase
{
    public function testEventsShouldBeDispatched(): void
    {
        $dispatcher = \Mockery::mock(EventDispatcherInterface::class);
        $dispatcher
            ->shouldReceive('dispatch')
            ->with(\Mockery::type(CommandStarting::class));
        $dispatcher
            ->shouldReceive('dispatch')
            ->with(\Mockery::type(InterceptorCalling::class));
        $dispatcher
            ->shouldReceive('dispatch')
            ->with(\Mockery::type(CommandFinished::class));

        $core = $this->getCore(
            locator: $this->getStaticLocator([new TestCommand()]),
            eventDispatcher: $dispatcher
        );

        $core->run('test');

        self::assertTrue(true);
    }
}
