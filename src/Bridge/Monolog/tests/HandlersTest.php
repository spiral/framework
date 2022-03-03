<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Tests\Monolog;

use Monolog\Handler\NullHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Spiral\Boot\BootloadManager;
use Spiral\Boot\FinalizerInterface;
use Spiral\Config\ConfigManager;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\LoaderInterface;
use Spiral\Core\Container;
use Spiral\Logger\ListenerRegistry;
use Spiral\Logger\ListenerRegistryInterface;
use Spiral\Logger\LogsInterface;
use Spiral\Monolog\Bootloader\MonologBootloader;
use Spiral\Monolog\Config\MonologConfig;
use Spiral\Monolog\Exception\ConfigException;

class HandlersTest extends TestCase
{
    /** @var Container */
    private $container;

    public function setUp(): void
    {
        $this->container = new Container();

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
        $this->container->bindSingleton(ListenerRegistryInterface::class, new ListenerRegistry());
        $this->container->get(BootloadManager::class)->bootload([MonologBootloader::class]);
    }

    public function testNoHandlers(): void
    {
        $this->container->bind(MonologConfig::class, new MonologConfig());

        $logger = $this->getLogger();
        $this->assertSame('test', $logger->getName());
        $this->assertCount(1, $logger->getHandlers());
    }

    public function testDefaultHandler(): void
    {
        $this->container->bind(MonologConfig::class, new MonologConfig([
            'globalHandler' => Logger::DEBUG
        ]));

        $logger = $this->getLogger();
        $this->assertSame('test', $logger->getName());
        $this->assertCount(1, $logger->getHandlers());
    }

    public function testInvalidHandler(): void
    {
        $this->expectException(ConfigException::class);

        $this->container->bind(MonologConfig::class, new MonologConfig([
            'globalHandler' => Logger::DEBUG,
            'handlers'      => [
                'test' => [
                    ['what?']
                ]
            ]
        ]));

        $this->getLogger();
    }

    public function testHandlerObject(): void
    {
        $this->container->bind(MonologConfig::class, new MonologConfig([
            'handlers' => [
                'test' => [
                    new Container\Autowire(NullHandler::class)
                ]
            ]
        ]));

        $logger = $this->getLogger();

        $this->assertCount(2, $logger->getHandlers());
        $this->assertInstanceOf(NullHandler::class, $logger->getHandlers()[0]);
    }

    public function testBindedHandler(): void
    {
        $this->container->bind('nullHandler', new NullHandler());
        $this->container->bind(MonologConfig::class, new MonologConfig([
            'handlers' => [
                'test' => [
                    'nullHandler'
                ]
            ]
        ]));

        $logger = $this->getLogger();

        $this->assertCount(2, $logger->getHandlers());
        $this->assertInstanceOf(NullHandler::class, $logger->getHandlers()[0]);
        $this->assertSame($this->container->get('nullHandler'), $logger->getHandlers()[0]);
    }

    public function testConstructHandler(): void
    {
        $this->container->bind(MonologConfig::class, new MonologConfig([
            'handlers' => [
                'test' => [
                    [
                        'class' => NullHandler::class
                    ]
                ]
            ]
        ]));

        $logger = $this->getLogger();

        $this->assertCount(2, $logger->getHandlers());
        $this->assertInstanceOf(NullHandler::class, $logger->getHandlers()[0]);
    }

    public function testConstructWithOptionsHandler(): void
    {
        $this->container->bind(MonologConfig::class, new MonologConfig([
            'handlers' => [
                'test' => [
                    [
                        'class'   => NullHandler::class,
                        'options' => [
                            'level' => Logger::CRITICAL
                        ]
                    ]
                ]
            ]
        ]));

        $logger = $this->getLogger();

        $this->assertCount(2, $logger->getHandlers());
        $this->assertInstanceOf(NullHandler::class, $logger->getHandlers()[0]);
        $this->assertFalse($logger->getHandlers()[0]->isHandling(['level' => Logger::DEBUG]));
        $this->assertTrue($logger->getHandlers()[0]->isHandling(['level' => Logger::CRITICAL]));
    }

    protected function getLogger(): Logger
    {
        return $this->container->get(LogsInterface::class)->getLogger('test');
    }
}
