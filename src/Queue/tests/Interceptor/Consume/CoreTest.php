<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Interceptor\Consume;

use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spiral\Queue\Event\JobProcessed;
use Spiral\Queue\Event\JobProcessing;
use Spiral\Queue\HandlerRegistryInterface;
use Spiral\Queue\Interceptor\Consume\Core;
use Spiral\Queue\JobHandler;
use Spiral\Tests\Queue\TestCase;

final class CoreTest extends TestCase
{
    #[DataProvider('payloadDataProvider')]
    public function testCallAction(mixed $payload): void
    {
        $core = new Core(
            $registry = m::mock(HandlerRegistryInterface::class)
        );

        $registry->shouldReceive('getHandler')->with('foo')->once()
            ->andReturn($handler = m::mock(JobHandler::class));

        $handler->shouldReceive('handle')
            ->once()
            ->with('foo', 'job-id', $payload, ['foo']);

        $core->callAction(
            controller: 'foo',
            action: 'bar',
            parameters: [
                'driver' => 'array',
                'queue' => 'default',
                'id' => 'job-id',
                'payload' => $payload,
                'headers' => ['foo'],
            ],
        );
    }

    #[DataProvider('payloadDataProvider')]
    public function testEventsShouldBeDispatched(mixed $payload): void
    {
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher
            ->expects(self::exactly(2))
            ->method('dispatch')
            ->with(
                $this->logicalOr(
                    new JobProcessing('foo', 'bar', 'other', 'id', $payload, []),
                    new JobProcessed('foo', 'bar', 'other', 'id', $payload, []),
                ),
            );

        $core = new Core(
            $registry = m::mock(HandlerRegistryInterface::class),
            $dispatcher
        );

        $registry->shouldReceive('getHandler')->with('foo')->once()
            ->andReturn($handler = m::mock(JobHandler::class));
        $handler->shouldReceive('handle')
            ->once()
            ->with('foo', 'id', $payload, []);

        $core->callAction('foo', 'bar', [
            'driver' => 'bar',
            'queue' => 'other',
            'id' => 'id',
            'payload' => $payload,
        ]);
    }

    public static function payloadDataProvider(): \Traversable
    {
        yield [['baz' => 'baf']];
        yield [new \stdClass()];
        yield ['some string'];
        yield [123];
        yield [null];
    }
}
