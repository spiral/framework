<?php

declare(strict_types=1);

namespace Spiral\Tests\Monolog;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Spiral\Boot\BootloadManager\StrategyBasedBootloadManager;
use Spiral\Boot\Finalizer;
use Spiral\Boot\FinalizerInterface;
use Spiral\Config\ConfigManager;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\LoaderInterface;
use Spiral\Core\Container;
use Spiral\Logger\LogsInterface;
use Spiral\Monolog\Bootloader\MonologBootloader;
use Spiral\Monolog\LogFactory;

class LoggerTest extends BaseTestCase
{
    use MockeryPHPUnitIntegration;

    public function testLoggerShouldBeReset(): void
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

        $logger = m::mock(Logger::class);
        $logger->shouldReceive('reset')->once();

        $this->container->get(StrategyBasedBootloadManager::class)->bootload([MonologBootloader::class]);

        $this->container->bind(LogsInterface::class, $factory = m::mock(LogsInterface::class));
        $factory->shouldReceive('getLogger')->once()->andReturn($logger);

        $finalizer->finalize();
    }
}
