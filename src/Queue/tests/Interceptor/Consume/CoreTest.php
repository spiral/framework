<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Interceptor\Consume;

use Mockery as m;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Queue\Event\JobProcessed;
use Spiral\Queue\Event\JobProcessing;
use Spiral\Queue\HandlerInterface;
use Spiral\Queue\HandlerRegistryInterface;
use Spiral\Queue\Interceptor\Consume\Core;
use Spiral\Tests\Queue\TestCase;

final class CoreTest extends TestCase
{
    public function testCallAction(): void
    {
        $core = new Core(
            $registry = m::mock(HandlerRegistryInterface::class)
        );

        $registry->shouldReceive('getHandler')->with('foo')->once()
            ->andReturn($handler = m::mock(HandlerInterface::class));

        $handler->shouldReceive('handle')->once()
            ->with('foo', 'job-id', ['baz' => 'baf'], ['foo']);

        $core->callAction(
            controller: 'foo',
            action: 'bar',
            parameters: [
                'driver' => 'array',
                'queue' => 'default',
                'id' => 'job-id',
                'payload' => ['baz' => 'baf'],
                'headers' => ['foo']
            ]
        );
    }

    public function testEventsShouldBeDispatched(): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::exactly(2))
            ->method('dispatch')
            ->with(
                $this->logicalOr(
                    new JobProcessing('foo', 'bar', 'other', 'id', [], []),
                    new JobProcessed('foo', 'bar', 'other', 'id', [], [])
                )
            );

        $core = new Core(
            $registry = m::mock(HandlerRegistryInterface::class),
            $dispatcher
        );

        $registry->shouldReceive('getHandler')->with('foo')->once()
            ->andReturn($handler = m::mock(HandlerInterface::class));
        $handler->shouldReceive('handle')->once()
            ->with('foo', 'id', [], []);

        $core->callAction('foo', 'bar', [
            'driver' => 'bar',
            'queue' => 'other',
            'id' => 'id',
            'payload' => [],
        ]);
    }
}
