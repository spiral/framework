<?php

declare(strict_types=1);

namespace Spiral\Tests\Monolog;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Spiral\Boot\BootloadManager\BootloadManager;
use Spiral\Boot\FinalizerInterface;
use Spiral\Config\ConfigManager;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\LoaderInterface;
use Spiral\Core\Container;
use Spiral\Monolog\Bootloader\MonologBootloader;

class RotateHandlerTest extends BaseTest
{
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
        $this->container->get(BootloadManager::class)->bootload([MonologBootloader::class]);

        $autowire = new Container\Autowire('log.rotate', [
            'filename' => 'monolog.log'
        ]);

        /** @var RotatingFileHandler $handler */
        $handler = $autowire->resolve($this->container);
        $this->assertInstanceOf(RotatingFileHandler::class, $handler);

        $this->assertSame(Logger::DEBUG, $handler->getLevel());
    }
}
