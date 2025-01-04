<?php

declare(strict_types=1);

namespace Spiral\Tests\Telemetry;

use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Spiral\Core\ScopeInterface;
use Spiral\Logger\LogsInterface;
use Spiral\Telemetry\ClockInterface;
use Spiral\Telemetry\LogTracer;
use Spiral\Telemetry\LogTracerFactory;

final class LogTracerFactoryTest extends TestCase
{
    public function testMake(): void
    {
        $logs = $this->createMock(LogsInterface::class);

        $logs->expects($this->once())
            ->method('getLogger')
            ->with('some-channel')
            ->willReturn($logger = $this->createMock(LoggerInterface::class));

        $factory = new LogTracerFactory(
            $scope = $this->createMock(ScopeInterface::class),
            $clock = $this->createMock(ClockInterface::class),
            $logs,
            'some-channel'
        );

        $clock->method('now');
        $logger->expects($this->once())->method('debug');

        self::assertInstanceOf(LogTracer::class, $tracer = $factory->make());

        $tracer->trace('foo', static fn(): string => 'hello');
    }
}

