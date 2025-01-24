<?php

declare(strict_types=1);

namespace Spiral\Tests\Logger;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Spiral\Core\Container;
use Spiral\Core\ContainerScope;
use Spiral\Logger\LogsInterface;
use Spiral\Logger\Traits\LoggerTrait;

class TraitTest extends TestCase
{
    use LoggerTrait;

    public function testNoScope(): void
    {
        $logger = $this->getLogger();
        self::assertInstanceOf(NullLogger::class, $this->getLogger());
        self::assertSame($logger, $this->getLogger());
    }

    public function testSetLogger(): void
    {
        $logger = new NullLogger();
        $this->setLogger($logger);
        self::assertSame($logger, $this->getLogger());
    }

    public function testGetsLoggerWhenChannelNotPassedAndContainerExistsButItDoesNotHaveLogsInterface(): void
    {
        $container = new Container();

        ContainerScope::runScope($container, function (): void {
            self::assertInstanceOf(NullLogger::class, $this->getLogger());
        });
    }

    public function testGetsLoggerWhenChannelNotPassedAndContainerExistsAndLogsInterfaceHasLogger(): void
    {
        $logsInterfaceLogger = new NullLogger();
        $logs = m::mock(LogsInterface::class);
        $logs->shouldReceive('getLogger')
            ->with(static::class)
            ->andReturn($logsInterfaceLogger);

        $container = new Container();
        $container->bind(LogsInterface::class, $logs);

        ContainerScope::runScope($container, function () use ($logsInterfaceLogger): void {
            self::assertEquals($logsInterfaceLogger, $this->getLogger());
        });
    }

    public function testGetsLoggerWhenChannelPassedAndContainerDoesNotExist(): void
    {
        self::assertInstanceOf(NullLogger::class, $this->getLogger('test-channel'));
    }

    public function testGetsLoggerWhenChannelPassedAndContainerExistsButItDoesNotHaveLogsInterface(): void
    {
        $container = new Container();

        ContainerScope::runScope($container, function (): void {
            self::assertInstanceOf(NullLogger::class, $this->getLogger('test-channel'));
        });
    }

    public function testGetsLoggerWhenChannelPassedAndContainerExistsAndLogsInterfaceHasLogger(): void
    {
        $logsInterfaceLogger = new NullLogger();
        $logs = m::mock(LogsInterface::class);
        $logs->shouldReceive('getLogger')
            ->with('test-channel')
            ->andReturn($logsInterfaceLogger);

        $container = new Container();
        $container->bind(LogsInterface::class, $logs);

        ContainerScope::runScope($container, function () use ($logsInterfaceLogger): void {
            self::assertEquals($logsInterfaceLogger, $this->getLogger('test-channel'));
        });
    }

    public function testGetsLoggerWhenChannelPassedAndLoggerSetButContainerDoesNotExists(): void
    {
        $logger = m::mock(LoggerInterface::class);
        $this->setLogger($logger);

        self::assertEquals($logger, $this->getLogger('test-channel'));
    }

    public function testGetsLoggerWhenChannelPassedAndLoggerSetAndContainerExistsButItDoesNotHaveLogsInterface(): void
    {
        $logger = m::mock(LoggerInterface::class);
        $this->setLogger($logger);
        $container = new Container();

        ContainerScope::runScope($container, function () use ($logger): void {
            self::assertEquals($logger, $this->getLogger('test-channel'));
        });
    }

    protected function setUp(): void
    {
        $this->logger = null;
    }
}
