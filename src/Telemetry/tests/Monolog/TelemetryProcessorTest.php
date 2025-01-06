<?php

declare(strict_types=1);

namespace Spiral\Tests\Telemetry\Monolog;

use Mockery as m;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunClassInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Spiral\Telemetry\Monolog\TelemetryProcessor;
use Spiral\Telemetry\TracerInterface;

#[RunClassInSeparateProcess]
final class TelemetryProcessorTest extends TestCase
{
    use m\Adapter\Phpunit\MockeryPHPUnitIntegration;

    public static function provideRecords(): iterable
    {
        $date = new \DateTimeImmutable('2025-01-06 09:55:42.986918');
        $record = new LogRecord($date, 'foo', Level::Debug, 'bar');

        yield [
            [
                'datetime' => $date,
                'channel' => 'foo',
                'level' => Level::Debug,
                'message' => 'bar',
                'context' => [],
                'extra' => [],
                'formatted' => null,
            ],
            ['foo' => 'bar'],
            [
                'datetime' => $date,
                'channel' => 'foo',
                'level' => Level::Debug,
                'message' => 'bar',
                'context' => [],
                'extra' => [
                    'telemetry' => [
                        'foo' => 'bar',
                    ],
                ],
                'formatted' => null,
            ],
        ];


        yield [
            [
                'datetime' => $date,
                'channel' => 'foo',
                'level' => Level::Debug,
                'message' => 'bar',
                'context' => [],
                'extra' => [],
                'formatted' => null,
            ],
            [],
            [
                'datetime' => $date,
                'channel' => 'foo',
                'level' => Level::Debug,
                'message' => 'bar',
                'context' => [],
                'extra' => [],
                'formatted' => null,
            ],
        ];

        yield [
            $record,
            ['foo' => 'bar'],
            [
                'datetime' => $date,
                'channel' => 'foo',
                'level' => Level::Debug,
                'message' => 'bar',
                'context' => [],
                'extra' => [
                    'telemetry' => [
                        'foo' => 'bar',
                    ],
                ],
                'formatted' => null,
            ],
        ];

        yield [
            $record,
            [],
            [
                'datetime' => $date,
                'channel' => 'foo',
                'level' => Level::Debug,
                'message' => 'bar',
                'context' => [],
                'extra' => [],
                'formatted' => null,
            ],
        ];
    }

    #[DataProvider('provideRecords')]
    public function testProcess(mixed $record, array $context, mixed $expected): void
    {
        $processor = new TelemetryProcessor(
            $container = m::mock(ContainerInterface::class),
        );

        $container
            ->shouldReceive('get')
            ->once()
            ->with(TracerInterface::class)
            ->andReturn($tracer = m::mock(TracerInterface::class));

        $tracer->shouldReceive('getContext')->once()->andReturn($context);

        $record = $processor->__invoke($record);

        self::assertSame($expected, (array) $record);
    }
}
