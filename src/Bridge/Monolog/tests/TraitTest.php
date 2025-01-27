<?php

declare(strict_types=1);

namespace Spiral\Tests\Monolog;

use Monolog\Logger;
use Psr\Log\NullLogger;
use Spiral\Boot\BootloadManager\StrategyBasedBootloadManager;
use Spiral\Boot\FinalizerInterface;
use Spiral\Config\ConfigManager;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\LoaderInterface;
use Spiral\Core\Container;
use Spiral\Core\ContainerScope;
use Spiral\Core\Scope;
use Spiral\Logger\ListenerRegistry;
use Spiral\Logger\ListenerRegistryInterface;
use Spiral\Logger\LogsInterface;
use Spiral\Logger\Traits\LoggerTrait;
use Spiral\Monolog\Bootloader\MonologBootloader;
use Spiral\Monolog\Config\MonologConfig;

final class TraitTest extends BaseTestCase
{
    use LoggerTrait;

    public function testNoScope(): void
    {
        $c = new Container();
        $c->runScope(new Scope(), function (): void {
            $logger = $this->getLogger();
            self::assertInstanceOf(NullLogger::class, $this->getLogger());
            self::assertSame($logger, $this->getLogger());
        });
    }

    public function testLoggerInScope(): void
    {
        $c = new Container();
        $mock = $this->createMock(LogsInterface::class);
        $logger = new \Spiral\Logger\NullLogger(static fn() => null, '');
        $mock->method('getLogger')->willReturn($logger);

        $c->bind(LogsInterface::class, $mock);

        $c->runScope(new Scope(), function (): void {
            $logger = $this->getLogger();
            self::assertInstanceOf(\Spiral\Logger\NullLogger::class, $this->getLogger());
            self::assertSame($logger, $this->getLogger());
        });
    }

    public function testSetLogger(): void
    {
        $logger = new NullLogger();
        $this->setLogger($logger);
        self::assertSame($logger, $this->getLogger());
    }

    public function testScope(): void
    {
        $this->container->bind(FinalizerInterface::class, $finalizer = \Mockery::mock(FinalizerInterface::class));
        $finalizer->shouldReceive('addFinalizer')->once();

        $this->container->bind(
            ConfiguratorInterface::class,
            new ConfigManager(
                new class implements LoaderInterface {
                    public function has(string $section): bool
                    {
                        return false;
                    }

                    public function load(string $section): array
                    {
                        return [];
                    }
                },
            ),
        );
        $this->container->get(StrategyBasedBootloadManager::class)->bootload([MonologBootloader::class]);
        $this->container->bind(MonologConfig::class, new MonologConfig());
        $this->container->bind(ListenerRegistryInterface::class, new ListenerRegistry());

        ContainerScope::runScope($this->container, function (): void {
            self::assertInstanceOf(Logger::class, $this->getLogger());
            self::assertSame(self::class, $this->getLogger()->getName());
        });
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = null;
    }
}
