<?php

declare(strict_types=1);

namespace Spiral\Tests\Telemetry;

use Psr\Container\ContainerInterface;
use Spiral\Core\Internal\Proxy;
use Spiral\Telemetry\Bootloader\TelemetryBootloader;
use Spiral\Telemetry\NullTracer;
use Spiral\Telemetry\SpanInterface;
use Spiral\Telemetry\TracerFactoryInterface;
use Spiral\Telemetry\TracerInterface;
use Spiral\Testing\TestCase;

final class ScopedTracerTest extends TestCase
{
    public function defineBootloaders(): array
    {
        return [
            TelemetryBootloader::class,
        ];
    }

    public function testFirstTracerIsProxy(): void
    {
        $tracer = $this->getContainer()->get(TracerInterface::class);

        $result = $tracer->trace(
            'foo',
            static fn(ContainerInterface $container): TracerInterface => $container
                ->get(TracerInterface::class),
        );

        self::assertTrue(Proxy::isProxy($tracer));
        self::assertFalse(Proxy::isProxy($result));
    }

    public function testTracerScopedBinding(): void
    {
        $this->getContainer()->bindSingleton(
            TracerFactoryInterface::class,
            $factory = $this->createMock(TracerFactoryInterface::class),
        );
        $factory->expects(self::exactly(1))
            ->method('make')
            ->willReturn(new NullTracer($this->getContainer()));

        $tracer = $this->getContainer()->get(TracerInterface::class);

        $result = $tracer->trace(
            'foo',
            static function (ContainerInterface $container): array {
                /** @var TracerInterface $tracer */
                $tracer = $container->get(TracerInterface::class);

                return [
                    $container->get(SpanInterface::class),
                    $tracer->trace(
                        'bar',
                        static fn(ContainerInterface $container): SpanInterface => $container
                            ->get(SpanInterface::class),
                        ['baz' => 42],
                    ),
                    $container->get(SpanInterface::class),
                ];
            },
            ['foo' => 'bar'],
        );

        self::assertSame($result[0], $result[2]);

        self::assertInstanceOf(SpanInterface::class, $result[0]);
        self::assertInstanceOf(SpanInterface::class, $result[1]);

        self::assertSame('foo', $result[0]->getName());
        self::assertSame('bar', $result[1]->getName());

        self::assertSame(['foo' => 'bar'], $result[0]->getAttributes());
        self::assertSame(['baz' => 42], $result[1]->getAttributes());
    }
}
