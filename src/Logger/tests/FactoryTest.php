<?php

declare(strict_types=1);

namespace Spiral\Tests\Logger;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Spiral\Boot\BootloadManager\DefaultInvokerStrategy;
use Spiral\Boot\BootloadManager\Initializer;
use Spiral\Boot\BootloadManager\InitializerInterface;
use Spiral\Boot\BootloadManager\InvokerStrategyInterface;
use Spiral\Boot\BootloadManager\StrategyBasedBootloadManager;
use Spiral\Boot\Environment;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Core\Container;
use Spiral\Logger\Attribute\LoggerChannel;
use Spiral\Logger\Bootloader\LoggerBootloader;
use Spiral\Logger\Event\LogEvent;
use Spiral\Logger\ListenerRegistry;
use Spiral\Logger\LogFactory;
use Spiral\Logger\LoggerInjector;
use Spiral\Logger\LogsInterface;

class FactoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected Container $container;

    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->bind(EnvironmentInterface::class, new Environment());
        $this->container->bind(InvokerStrategyInterface::class, DefaultInvokerStrategy::class);
        $this->container->bind(InitializerInterface::class, Initializer::class);
    }

    #[DoesNotPerformAssertions]
    public function testDefaultLogger(): void
    {
        $factory = new LogFactory(new ListenerRegistry());
        $factory->getLogger('default');
    }

    public function testInjection(): void
    {
        $factory = new class () implements LogsInterface {
            public function getLogger(string $channel): LoggerInterface
            {
                $mock = \Mockery::mock(LoggerInterface::class);
                $mock->shouldReceive('getName')->andReturn($channel);
                return $mock;
            }
        };
        $this->container->get(StrategyBasedBootloadManager::class)->bootload([LoggerBootloader::class]);
        $this->container->bindSingleton(LogsInterface::class, $factory);

        $this->assertInstanceOf(LoggerInterface::class, $logger = $this->container->get(LoggerInterface::class));
        $this->assertSame(LoggerInjector::DEFAULT_CHANNEL, $logger->getName());
    }

    public function testInjectionNullableChannel(): void
    {
        $factory = new class () implements LogsInterface {
            public function getLogger(?string $channel): LoggerInterface
            {
                $mock = \Mockery::mock(LoggerInterface::class);
                $mock->shouldReceive('getName')->andReturn($channel);
                return $mock;
            }
        };
        $this->container->get(StrategyBasedBootloadManager::class)->bootload([LoggerBootloader::class]);
        $this->container->bindSingleton(LogsInterface::class, $factory);

        $this->assertInstanceOf(LoggerInterface::class, $logger = $this->container->get(LoggerInterface::class));
        $this->assertNull($logger->getName());
    }

    public function testInjectionWithAttribute(): void
    {
        $factory = new class () implements LogsInterface {
            public function getLogger(?string $channel): LoggerInterface
            {
                $mock = \Mockery::mock(LoggerInterface::class);
                $mock->shouldReceive('getName')->andReturn($channel);
                return $mock;
            }
        };
        $this->container->get(StrategyBasedBootloadManager::class)->bootload([LoggerBootloader::class]);
        $this->container->bindSingleton(LogsInterface::class, $factory);

        $this->container->invoke(function (#[LoggerChannel('foo')] LoggerInterface $logger): void {
            $this->assertSame('foo', $logger->getName());
        });
    }


    public function testEvent(): void
    {
        $l = new ListenerRegistry();
        $l->addListener(function (LogEvent $event): void {
            $this->assertSame('error', $event->getMessage());
            $this->assertSame('default', $event->getChannel());
            $this->assertSame(LogLevel::CRITICAL, $event->getLevel());
        });

        $f = new LogFactory($l);

        $l = $f->getLogger('default');

        $l->critical('error');
    }
}
