<?php

declare(strict_types=1);

namespace Spiral\Tests\Telemetry;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Spiral\Core\InvokerInterface;
use Spiral\Core\ScopeInterface;
use Spiral\Telemetry\ClockInterface;
use Spiral\Telemetry\LogTracer;
use Spiral\Telemetry\Span;
use Spiral\Telemetry\SpanInterface;

final class LogTracerTest extends TestCase
{
    public function testTrace(): void
    {
        $tracer = new LogTracer(
            $scope = m::mock(ScopeInterface::class),
            $clock = m::mock(ClockInterface::class),
            $logger = m::mock(LoggerInterface::class)
        );

        $invoker = m::mock(InvokerInterface::class);

        $callable = fn() => 'hello';

        $invoker->shouldReceive('invoke')
            ->once()
            ->with($callable)
            ->andReturn('hello');

        $clock->shouldReceive('now');
        $logger->shouldReceive('debug')->once();

        $scope->shouldReceive('runScope')
            ->withArgs(fn(array $scope) =>
                $scope[SpanInterface::class] instanceof Span
                && $scope[SpanInterface::class]->getName() === 'foo'
            )
            ->andReturnUsing(fn(array $scope, callable $callable) => $callable($invoker));

        $this->assertSame(
            'hello',
            $tracer->trace('foo', $callable, ['foo' => 'bar'])
        );
    }
}
