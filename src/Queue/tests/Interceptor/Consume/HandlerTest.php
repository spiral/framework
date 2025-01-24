<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Interceptor;

use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;
use Spiral\Core\CoreInterface;
use Spiral\Queue\Interceptor\Consume\Handler;
use Spiral\Telemetry\NullTracer;
use Spiral\Telemetry\TracerFactoryInterface;
use Spiral\Tests\Queue\TestCase;

final class HandlerTest extends TestCase
{
    public static function payloadDataProvider(): \Traversable
    {
        yield [['baz' => 'baf']];
        yield [new \stdClass()];
        yield ['some string'];
        yield [123];
        yield [null];
    }

    #[DataProvider('payloadDataProvider')]
    public function testHandle(mixed $payload): void
    {
        $tracerFactory = m::mock(TracerFactoryInterface::class);

        $tracerFactory->shouldReceive('make')
            ->once()
            ->with(['some' => 'data'])
            ->andReturn($tracer = new NullTracer());

        $handler = new Handler(
            core: $core = m::mock(CoreInterface::class),
            tracerFactory: $tracerFactory,
        );

        $core->shouldReceive('callAction')
            ->once()
            ->with('foo', 'handle', [
                'driver' => 'sync',
                'queue' => 'default',
                'id' => 'job-id',
                'payload' => $payload,
                'headers' => ['some' => 'data'],
            ]);

        $handler->handle('foo', 'sync', 'default', 'job-id', $payload, ['some' => 'data']);
    }
}
