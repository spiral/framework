<?php

declare(strict_types=1);

namespace Spiral\Tests\Monolog;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Monolog\Processor\ProcessorInterface;
use Monolog\ResettableInterface;
use Psr\Log\LoggerInterface;
use Spiral\Boot\BootloadManager\StrategyBasedBootloadManager;
use Spiral\Boot\Finalizer;
use Spiral\Boot\FinalizerInterface;
use Spiral\Config\ConfigManager;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\LoaderInterface;
use Spiral\Core\Container;
use Spiral\Logger\ListenerRegistry;
use Spiral\Logger\LogsInterface;
use Spiral\Monolog\Bootloader\MonologBootloader;
use Spiral\Monolog\Config\MonologConfig;
use Spiral\Monolog\LogFactory;

class FactoryTest extends BaseTest
{
    use MockeryPHPUnitIntegration;

    public function testDefaultLogger(): void
    {
        $factory = new LogFactory(new MonologConfig([]), new ListenerRegistry(),$this->container);
        $logger = $factory->getLogger();

        $this->assertNotEmpty($logger);
        $this->assertSame($logger, $factory->getLogger());
        $this->assertSame($logger, $factory->getLogger(MonologConfig::DEFAULT_CHANNEL));
    }

    public function testChangedDefaultLogger(): void
    {
        $factory = new LogFactory(new MonologConfig(['default' => 'foo']), new ListenerRegistry(), $this->container);

        $logger = $factory->getLogger();

        $this->assertNotEmpty($logger);
        $this->assertSame($logger, $factory->getLogger());
        $this->assertSame($logger, $factory->getLogger('foo'));
    }

    public function testInjection(): void
    {
        $factory = new LogFactory(new MonologConfig([]), new ListenerRegistry(), new Container());
        $logger = $factory->getLogger();

        $this->container->bind(ConfiguratorInterface::class, new ConfigManager(
            new class() implements LoaderInterface {
                public function has(string $section): bool
                {
                    return false;
                }

                public function load(string $section): array
                {
                    return [];
                }
            }
        ));

        $this->container->bind(FinalizerInterface::class, $finalizer = \Mockery::mock(FinalizerInterface::class));
        $finalizer->shouldReceive('addFinalizer')->once();

        $this->container->get(StrategyBasedBootloadManager::class)->bootload([MonologBootloader::class]);
        $this->container->bind(LogFactory::class, $factory);

        $this->assertSame($logger, $this->container->get(Logger::class));
        $this->assertSame($logger, $this->container->get(LoggerInterface::class));
    }

    public function testFinalizerShouldResetDefaultLogger()
    {
        $this->container->bind(ConfiguratorInterface::class, new ConfigManager(
            new class() implements LoaderInterface {
                public function has(string $section): bool
                {
                    return false;
                }

                public function load(string $section): array
                {
                    return [];
                }
            }
        ));

        $this->container->bind(FinalizerInterface::class, $finalizer = new Finalizer());

        $factory = new LogFactory(new MonologConfig([
            'handlers' => [
                'default' => [
                    $handler = \Mockery::mock(HandlerInterface::class, ResettableInterface::class)
                ]
            ],
            'processors' => [
                'default' => [
                    $processor = \Mockery::mock(ProcessorInterface::class, ResettableInterface::class)
                ]
            ]
        ]), new ListenerRegistry(), $this->container);

        $handler->shouldReceive('reset')->once();
        $processor->shouldReceive('reset')->once();

        $this->container->bind(LogFactory::class, $factory);
        $this->container->get(StrategyBasedBootloadManager::class)->bootload([MonologBootloader::class]);
        $this->container->get(LogsInterface::class)->getLogger();
        $finalizer->finalize();
    }

    public function testFinalizerShouldNotResetLoggerWhenApplicationTerminating()
    {
        $this->container->bind(ConfiguratorInterface::class, new ConfigManager(
            new class() implements LoaderInterface {
                public function has(string $section): bool
                {
                    return false;
                }

                public function load(string $section): array
                {
                    return [];
                }
            }
        ));

        $this->container->bind(FinalizerInterface::class, $finalizer = new Finalizer());

        $factory = new LogFactory(new MonologConfig([
            'handlers' => [
                'default' => [
                    $handler = \Mockery::mock(HandlerInterface::class, ResettableInterface::class)
                ]
            ],
            'processors' => [
                'default' => [
                    $processor = \Mockery::mock(ProcessorInterface::class, ResettableInterface::class)
                ]
            ]
        ]), new ListenerRegistry(), $this->container);

        $handler->shouldReceive('reset')->never();
        $processor->shouldReceive('reset')->never();

        $this->container->bind(LogFactory::class, $factory);
        $this->container->get(StrategyBasedBootloadManager::class)->bootload([MonologBootloader::class]);
        $this->container->get(LogsInterface::class)->getLogger();
        $finalizer->finalize(true);
    }
}
