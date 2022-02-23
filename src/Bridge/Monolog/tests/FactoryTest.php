<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Monolog;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Monolog\Processor\ProcessorInterface;
use Monolog\ResettableInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Spiral\Boot\BootloadManager;
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

class FactoryTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testDefaultLogger(): void
    {
        $factory = new LogFactory(new MonologConfig([]), new ListenerRegistry(), new Container());
        $logger = $factory->getLogger();

        $this->assertNotEmpty($logger);
        $this->assertSame($logger, $factory->getLogger());
        $this->assertSame($logger, $factory->getLogger(LogFactory::DEFAULT));
    }

    public function testInjection(): void
    {
        $factory = new LogFactory(new MonologConfig([]), new ListenerRegistry(), new Container());
        $logger = $factory->getLogger();

        $container = new Container();
        $container->bind(ConfiguratorInterface::class, new ConfigManager(
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

        $container->bind(FinalizerInterface::class, $finalizer = \Mockery::mock(FinalizerInterface::class));
        $finalizer->shouldReceive('addFinalizer')->once();

        $container->get(BootloadManager::class)->bootload([MonologBootloader::class]);
        $container->bind(LogFactory::class, $factory);

        $this->assertSame($logger, $container->get(Logger::class));
        $this->assertSame($logger, $container->get(LoggerInterface::class));
    }

    public function testFinalizerShouldResetDefaultLogger()
    {
        $container = new Container();
        $container->bind(ConfiguratorInterface::class, new ConfigManager(
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

        $container->bind(FinalizerInterface::class, $finalizer = new Finalizer());

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
        ]), new ListenerRegistry(), $container);

        $handler->shouldReceive('reset')->once();
        $processor->shouldReceive('reset')->once();

        $container->bind(LogFactory::class, $factory);
        $container->get(BootloadManager::class)->bootload([MonologBootloader::class]);
        $container->get(LogsInterface::class)->getLogger();
        $finalizer->finalize();
    }
}
