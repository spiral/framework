<?php

declare(strict_types=1);

namespace Spiral\Tests\Monolog;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Spiral\Boot\BootloadManager\DefaultInvokerStrategy;
use Spiral\Boot\BootloadManager\Initializer;
use Spiral\Boot\BootloadManager\InitializerInterface;
use Spiral\Boot\BootloadManager\InvokerStrategyInterface;
use Spiral\Boot\BootloadManager\StrategyBasedBootloadManager;
use Spiral\Boot\Environment;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Boot\FinalizerInterface;
use Spiral\Config\ConfigManager;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\LoaderInterface;
use Spiral\Core\Container;
use Spiral\Monolog\Bootloader\MonologBootloader;

class RotateHandlerTest extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->container->bind(InvokerStrategyInterface::class, DefaultInvokerStrategy::class);
        $this->container->bind(InitializerInterface::class, Initializer::class);
    }

    public function testRotateHandler(): void
    {
        $this->container->bind(FinalizerInterface::class, $finalizer = \Mockery::mock(FinalizerInterface::class));
        $finalizer->shouldReceive('addFinalizer')->once();

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
        $this->container->get(StrategyBasedBootloadManager::class)->bootload([MonologBootloader::class]);

        $autowire = new Container\Autowire('log.rotate', [
            'filename' => 'monolog.log'
        ]);

        /** @var RotatingFileHandler $handler */
        $handler = $autowire->resolve($this->container);
        $this->assertInstanceOf(RotatingFileHandler::class, $handler);

        $this->assertSame(Logger::DEBUG, $handler->getLevel());
        $this->assertTrue($handler->getBubble());
    }

    public function testChangeFormat(): void
    {
        $this->container->bind(FinalizerInterface::class, $finalizer = \Mockery::mock(FinalizerInterface::class));
        $finalizer->shouldReceive('addFinalizer')->once();

        $this->container->bind(EnvironmentInterface::class, new Environment(['MONOLOG_FORMAT' => 'foo']));
        $this->container->bind(ConfiguratorInterface::class, $this->createMock(ConfiguratorInterface::class));
        $this->container->get(StrategyBasedBootloadManager::class)->bootload([MonologBootloader::class]);

        $autowire = new Container\Autowire('log.rotate', [
            'filename' => 'monolog.log'
        ]);

        /** @var RotatingFileHandler $handler */
        $handler = $autowire->resolve($this->container);
        $this->assertInstanceOf(RotatingFileHandler::class, $handler);

        $this->assertSame(Logger::DEBUG, $handler->getLevel());

        $formatter = $handler->getFormatter();
        $this->assertSame('foo', (new \ReflectionProperty($formatter, 'format'))->getValue($formatter));
    }
}
