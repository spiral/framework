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
use Spiral\Core\ContainerScope;
use Spiral\Logger\ListenerRegistry;
use Spiral\Logger\ListenerRegistryInterface;
use Spiral\Logger\Traits\LoggerTrait;
use Spiral\Monolog\Bootloader\MonologBootloader;
use Spiral\Monolog\Config\MonologConfig;

class TraitTest extends BaseTestCase
{
    use LoggerTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->logger = null;
    }

    public function testNoScope(): void
    {
        $logger = $this->getLogger();
        $this->assertInstanceOf(NullLogger::class, $this->getLogger());
        $this->assertSame($logger, $this->getLogger());
    }

    public function testSetLogger(): void
    {
        $logger = new NullLogger();
        $this->setLogger($logger);
        $this->assertSame($logger, $this->getLogger());
    }

    public function testScope(): void
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
        $this->container->bind(MonologConfig::class, new MonologConfig());
        $this->container->bind(ListenerRegistryInterface::class, new ListenerRegistry());

        ContainerScope::runScope($this->container, function (): void {
            $this->assertInstanceOf(Logger::class, $this->getLogger());
            $this->assertSame(self::class, $this->getLogger()->getName());
        });
    }
}
