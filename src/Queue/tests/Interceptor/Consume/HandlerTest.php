<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Interceptor;

use Mockery as m;
use Spiral\Core\CoreInterface;
use Spiral\Core\ScopeInterface;
use Spiral\Queue\Interceptor\Consume\Handler;
use Spiral\Telemetry\NullTracer;
use Spiral\Telemetry\TracerFactoryInterface;
use Spiral\Telemetry\TracerInterface;
use Spiral\Tests\Queue\TestCase;

final class HandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $tracerFactory = m::mock(TracerFactoryInterface::class);

        $tracerFactory->shouldReceive('make')
            ->once()
            ->with(['some' => 'data'])
            ->andReturn( $tracer = new NullTracer());

        $handler = new Handler(
            core: $core = m::mock(CoreInterface::class),
            tracerFactory: $tracerFactory
        );

        $core->shouldReceive('callAction')
            ->once()
            ->with('foo', 'handle', [
                'driver' => 'sync',
                'queue' => 'default',
                'id' => 'job-id',
                'payload' => ['baz' => 'bar'],
                'headers' => ['some' => 'data'],
            ]);

        $handler->handle('foo', 'sync', 'default', 'job-id', ['baz' => 'bar'], ['some' => 'data']);
    }
}
