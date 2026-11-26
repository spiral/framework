<?php

declare(strict_types=1);

namespace Spiral\Tests\Telemetry;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Ramsey\Uuid\UuidFactory;
use Spiral\Core\Container;
use Spiral\Core\ContainerScope;
use Spiral\Telemetry\CurrentSpan;
use Spiral\Telemetry\Clock\SystemClock;
use Spiral\Telemetry\LogTracer;
use Spiral\Telemetry\NullTracer;
use Spiral\Telemetry\Span;
use Spiral\Telemetry\Span\ActiveTrace;
use Spiral\Telemetry\Span\NoopSpan;
use Spiral\Telemetry\SpanInterface;
use Spiral\Tests\Telemetry\Stub\FiberStateTracer;
use Spiral\Tests\Telemetry\Stub\ProvidingTracer;

final class CurrentSpanTest extends TestCase
{
    public static function provideScoped(): iterable
    {
        yield 'not scoped' => [false];
        yield 'scoped' => [true];
    }

    public static function provideContainerScope(): iterable
    {
        yield 'no container scope' => [false];
        yield 'in container scope' => [true];
    }

    public function testTraceInFiberPassesSuspendAndResumeValues(): void
    {
        $tracer = new NullTracer(new Container());
        $fiber = new \Fiber(static fn(): array => $tracer->trace(
            'foo',
            static fn(): array => [\Fiber::suspend('out'), CurrentSpan::get()->getName()],
        ));

        self::assertSame('out', $fiber->start());
        $fiber->resume('in');

        self::assertSame(['in', 'foo'], $fiber->getReturn());
    }

    public function testExceptionThrownIntoFiberReachesTraceWithItsSpan(): void
    {
        $tracer = new NullTracer(new Container());
        $fiber = new \Fiber(static fn(): array => $tracer->trace('foo', static function (): array {
            try {
                \Fiber::suspend();
            } catch (\RuntimeException $e) {
                $caught = [$e->getMessage(), CurrentSpan::get()->getName()];
            }
            \Fiber::suspend();

            return [...$caught ?? [], CurrentSpan::get()->getName()];
        }));

        $fiber->start();
        $fiber->throw(new \RuntimeException('boom'));
        $whileSuspended = CurrentSpan::get();
        $fiber->resume();

        self::assertSame(['boom', 'foo', 'foo'], $fiber->getReturn());
        self::assertInstanceOf(NoopSpan::class, $whileSuspended);
    }

    public function testFiberResumedInsideAnotherTraceKeepsThatTrace(): void
    {
        $tracer = new NullTracer(new Container());
        $fiber = new \Fiber(static fn() => $tracer->trace('fiber', static function (): void {
            \Fiber::suspend();
            \Fiber::suspend();
        }));
        $fiber->start();

        $names = $tracer->trace('main', static function () use ($fiber): array {
            $names = [CurrentSpan::get()->getName()];
            $fiber->resume();
            $names[] = CurrentSpan::get()->getName();
            $fiber->resume();
            $names[] = CurrentSpan::get()->getName();

            return $names;
        });

        self::assertSame(['main', 'main', 'main'], $names);
        self::assertInstanceOf(NoopSpan::class, CurrentSpan::get());
    }

    public function testFiberStartedInsideEndedTraceKeepsNoEndedTraceCurrent(): void
    {
        $tracer = new NullTracer(new Container());
        $fiber = $tracer->trace('ended', static function () use ($tracer): \Fiber {
            $fiber = new \Fiber(static fn() => $tracer->trace('fiber', static fn() => \Fiber::suspend()));
            $fiber->start();

            return $fiber;
        });

        $name = $tracer->trace('root', static fn(): string => $tracer->trace('main', static function () use ($fiber): string {
            $fiber->resume();

            return CurrentSpan::get()->getName();
        }));

        self::assertSame('main', $name);
        self::assertInstanceOf(NoopSpan::class, CurrentSpan::get());
    }

    public function testFiberDestroyedWhileSuspendedInTraceKeepsCurrentTraceOfOthers(): void
    {
        $tracer = new NullTracer(new Container());
        $fiber = new \Fiber(static fn() => $tracer->trace('fiber', static fn() => \Fiber::suspend()));
        $fiber->start();

        $name = $tracer->trace('main', static function () use (&$fiber): string {
            $fiber = null;

            return CurrentSpan::get()->getName();
        });

        self::assertSame('main', $name);
        self::assertInstanceOf(NoopSpan::class, CurrentSpan::get());
    }

    public function testOutsideTraceGivesNoopSpan(): void
    {
        $span = CurrentSpan::get()->setAttribute('foo', 'bar')->setAttribute('baz', 1);

        self::assertSame(NoopSpan::instance(), $span);
        self::assertSame([], $span->getAttributes());
    }

    #[DataProvider('provideScoped')]
    public function testInsideTraceGivesSpanOfTrace(bool $scoped): void
    {
        $tracer = new NullTracer(new Container());

        $spans = $tracer->trace(
            'foo',
            static fn(SpanInterface $span): array => [$span, CurrentSpan::get()],
            scoped: $scoped,
        );

        self::assertInstanceOf(Span::class, $spans[0]);
        self::assertSame($spans[0], $spans[1]);
        self::assertInstanceOf(NoopSpan::class, CurrentSpan::get());
    }

    public function testInsideTraceOfLogTracer(): void
    {
        $tracer = new LogTracer(
            new Container(),
            new SystemClock(),
            new NullLogger(),
            new UuidFactory(),
        );

        $spans = $tracer->trace('foo', static fn(SpanInterface $span): array => [$span, CurrentSpan::get()]);

        self::assertSame($spans[0], $spans[1]);
    }

    public function testInsideTraceInContainerScope(): void
    {
        $container = new Container();
        $tracer = new NullTracer($container);

        $spans = ContainerScope::runScope(
            $container,
            static fn(): array => $tracer->trace(
                'foo',
                static fn(SpanInterface $span): array => [$span, CurrentSpan::get()],
            ),
        );

        self::assertSame($spans[0], $spans[1]);
    }

    public function testNestedTraces(): void
    {
        $tracer = new NullTracer(new Container());

        $names = $tracer->trace('outer', static fn(): array => [
            CurrentSpan::get()->getName(),
            $tracer->trace('inner', static fn(): string => CurrentSpan::get()->getName()),
            CurrentSpan::get()->getName(),
        ]);

        self::assertSame(['outer', 'inner', 'outer'], $names);
    }

    public function testOuterSpanAfterInnerTraceThrows(): void
    {
        $tracer = new NullTracer(new Container());

        $name = $tracer->trace('outer', static function () use ($tracer): string {
            try {
                $tracer->trace('inner', static fn() => throw new \RuntimeException());
            } catch (\RuntimeException) {
            }

            return CurrentSpan::get()->getName();
        });

        self::assertSame('outer', $name);
    }

    public function testNoopSpanAfterTraceThrows(): void
    {
        $tracer = new NullTracer(new Container());

        try {
            $tracer->trace('foo', static fn() => throw new \RuntimeException());
        } catch (\RuntimeException) {
        }

        self::assertInstanceOf(NoopSpan::class, CurrentSpan::get());
    }

    public function testProviderGivesItsSpan(): void
    {
        $own = new Span('own');
        $tracer = new ProvidingTracer(static fn(): SpanInterface => $own);

        self::assertSame($own, $tracer->trace('foo', static fn(): SpanInterface => CurrentSpan::get()));
    }

    public function testProviderGivingNullFallsBackToSpanOfTrace(): void
    {
        $tracer = new ProvidingTracer(static fn(): ?SpanInterface => null);

        self::assertSame('foo', $tracer->trace('foo', static fn(): string => CurrentSpan::get()->getName()));
    }

    public function testThrowingProviderFallsBackToSpanOfTrace(): void
    {
        $tracer = new ProvidingTracer(static fn() => throw new \RuntimeException());

        self::assertSame('foo', $tracer->trace('foo', static fn(): string => CurrentSpan::get()->getName()));
    }

    public function testProviderOfOuterTraceIsNotAskedInsideTraceOfAnotherTracer(): void
    {
        $outer = new ProvidingTracer(static fn(): SpanInterface => new Span('provided'));
        $inner = new NullTracer(new Container());

        $name = $outer->trace(
            'outer',
            static fn(): string => $inner->trace('inner', static fn(): string => CurrentSpan::get()->getName()),
        );

        self::assertSame('inner', $name);
    }

    public function testSetProviderGivesItsSpanOutsideTrace(): void
    {
        $own = new Span('own');
        CurrentSpan::setProvider(new ProvidingTracer(static fn(): SpanInterface => $own));

        self::assertSame($own, CurrentSpan::get());
    }

    public function testSetProviderGivingNullOrThrowingFallsBackToNoopSpan(): void
    {
        CurrentSpan::setProvider(new ProvidingTracer(static fn(): ?SpanInterface => null));
        self::assertInstanceOf(NoopSpan::class, CurrentSpan::get());

        CurrentSpan::setProvider(new ProvidingTracer(static fn() => throw new \RuntimeException()));
        self::assertInstanceOf(NoopSpan::class, CurrentSpan::get());
    }

    public function testSetProviderIsNotAskedInsideTrace(): void
    {
        CurrentSpan::setProvider(new ProvidingTracer(static fn(): SpanInterface => new Span('provided')));
        $tracer = new NullTracer(new Container());

        self::assertSame('foo', $tracer->trace('foo', static fn(): string => CurrentSpan::get()->getName()));
    }

    public function testSetProviderUnsetWithNull(): void
    {
        CurrentSpan::setProvider(new ProvidingTracer(static fn(): SpanInterface => new Span('provided')));
        CurrentSpan::setProvider(null);

        self::assertInstanceOf(NoopSpan::class, CurrentSpan::get());
    }

    public function testConcurrentTracesInFibersSeeTheirOwnSpans(): void
    {
        $container = new Container();
        $tracer = new NullTracer($container);
        $trace = static fn(string $name): \Fiber => new \Fiber(
            static fn(): array => $tracer->trace($name, static function (): array {
                $before = CurrentSpan::get()->getName();
                \Fiber::suspend();

                return [$before, CurrentSpan::get()->getName()];
            }),
        );

        $names = ContainerScope::runScope($container, static function () use ($trace): array {
            $a = $trace('a');
            $b = $trace('b');
            $a->start();
            $b->start();
            $outside = CurrentSpan::get();
            $a->resume();
            $b->resume();

            return [$a->getReturn(), $b->getReturn(), $outside];
        });

        self::assertSame(['a', 'a'], $names[0]);
        self::assertSame(['b', 'b'], $names[1]);
        self::assertInstanceOf(NoopSpan::class, $names[2]);
    }

    public function testFiberStartedInsideTraceGetsIt(): void
    {
        $tracer = new NullTracer(new Container());

        $names = $tracer->trace('outer', static function () use ($tracer): array {
            $fiber = new \Fiber(static function () use ($tracer): array {
                $before = CurrentSpan::get()->getName();
                $inner = $tracer->trace('inner', static function (): string {
                    \Fiber::suspend();

                    return CurrentSpan::get()->getName();
                });

                return [$before, $inner, CurrentSpan::get()->getName()];
            });
            $fiber->start();
            $whileSuspended = CurrentSpan::get()->getName();
            $fiber->resume();

            return [...$fiber->getReturn(), $whileSuspended];
        });

        self::assertSame(['outer', 'inner', 'outer', 'outer'], $names);
    }

    #[DataProvider('provideContainerScope')]
    public function testTracerHandsItsPerFiberStateIntoFibersOfTraceByWrappingCallback(bool $inContainerScope): void
    {
        $tracer = new FiberStateTracer();
        $traces = static fn(): array => $tracer->trace('outer', static function () use ($tracer): array {
            $state = $tracer->state();
            \Fiber::suspend();
            $inner = $tracer->trace('inner', static fn(): array => [$tracer->state(), CurrentSpan::get()->getName()]);

            return [$state, $tracer->state(), CurrentSpan::get()->getName(), ...$inner];
        });

        $fiber = new \Fiber(
            $inContainerScope ? static fn(): array => ContainerScope::runScope(new Container(), $traces) : $traces,
        );
        $fiber->start();
        $fiber->resume();

        self::assertSame(['outer', 'outer', 'outer', 'inner', 'inner'], $fiber->getReturn());
    }

    protected function tearDown(): void
    {
        CurrentSpan::setProvider(null);
        self::assertNull(ActiveTrace::current());
    }
}
