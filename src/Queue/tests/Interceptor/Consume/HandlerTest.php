<?php

declare(strict_types=1);

namespace Spiral\Tests\Queue\Interceptor;

use Mockery as m;
use Spiral\Core\Container;
use Spiral\Core\CoreInterface;
use Spiral\Queue\Interceptor\Consume\Handler;
use Spiral\Telemetry\NullTracer;
use Spiral\Telemetry\TracerFactory;
use Spiral\Telemetry\TracerInterface;
use Spiral\Tests\Queue\TestCase;

final class HandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $container = new Container();
        $container->bind(TracerInterface::class, new NullTracer($container));

        $handler = new Handler(
            $core = m::mock(CoreInterface::class),
            $container,
            new TracerFactory($container)
        );

        $core->shouldReceive('callAction')
            ->once()
            ->with('foo', 'handle', [
                'driver' => 'sync',
                'queue' => 'default',
                'id' => 'job-id',
                'payload' => ['baz' => 'bar'],
                'context' => ['some' => 'data'],
            ]);

        $handler->handle('foo', 'sync', 'default', 'job-id', ['baz' => 'bar'], ['some' => 'data']);
    }
}
