<?php

declare(strict_types=1);

namespace Spiral\Tests\Telemetry;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactoryInterface;
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
            $logger = m::mock(LoggerInterface::class),
            $uuid = m::mock(UuidFactoryInterface::class),
        );

        $invoker = m::mock(InvokerInterface::class);

        $uuid->shouldReceive('uuid4')->once()->andReturn($uuid = Uuid::uuid4());

        $callable = static fn(): string => 'hello';

        $invoker->shouldReceive('invoke')
            ->once()
            ->with($callable)
            ->andReturn('hello');

        $clock->shouldReceive('now');
        $logger->shouldReceive('debug')->once();

        $scope->shouldReceive('runScope')
            ->withArgs(
                static fn(array $scope): bool =>
                $scope[SpanInterface::class] instanceof Span
                && $scope[SpanInterface::class]->getName() === 'foo',
            )
            ->andReturnUsing(fn(array $scope, callable $callable) => $callable($invoker));

        self::assertSame('hello', $tracer->trace('foo', $callable, ['foo' => 'bar']));
        self::assertSame(['telemetry' => $uuid->toString()], $tracer->getContext());
    }
}
