<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Monolog;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Spiral\Boot\BootloadManager;
use Spiral\Config\ConfigManager;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\LoaderInterface;
use Spiral\Core\Container;
use Spiral\Monolog\Bootloader\MonologBootloader;

class RotateHandlerTest extends TestCase
{
    public function testRotateHandler(): void
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
        $container->get(BootloadManager::class)->bootload([MonologBootloader::class]);

        $autowire = new Container\Autowire('log.rotate', [
            'filename' => 'monolog.log'
        ]);

        /** @var RotatingFileHandler $handler */
        $handler = $autowire->resolve($container);
        $this->assertInstanceOf(RotatingFileHandler::class, $handler);

        $this->assertSame(Logger::DEBUG, $handler->getLevel());
    }
}
