<?php

declare(strict_types=1);

namespace Spiral\Tests\Telemetry;

use Mockery as m;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Spiral\Core\InvokerInterface;
use Spiral\Core\ScopeInterface;
use Spiral\Telemetry\NullTracer;
use Spiral\Telemetry\Span;
use Spiral\Telemetry\SpanInterface;

final class NullTracerTest extends TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    #[RunInSeparateProcess]
    public function testFallbackRunScope(): void
    {
        $tracer = new NullTracer(
            $scope = m::mock(ScopeInterface::class),
        );

        $invoker = m::mock(InvokerInterface::class);

        $callable = static fn(): string => 'hello';

        $invoker->shouldReceive('invoke')
            ->once()
            ->with($callable)
            ->andReturn('hello');

        $scope->shouldReceive('runScope')
            ->withArgs(
                static fn(array $scope): bool =>
                $scope[SpanInterface::class] instanceof Span
                && $scope[SpanInterface::class]->getName() === 'foo',
            )
            ->andReturnUsing(static fn(array $scope, callable $callable) => $callable($invoker));

        self::assertSame('hello', $tracer->trace('foo', $callable, ['foo' => 'bar']));
    }
}
