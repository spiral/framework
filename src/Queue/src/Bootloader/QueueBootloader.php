<?php

declare(strict_types=1);

namespace Spiral\Queue\Bootloader;

use Psr\Container\ContainerInterface;
use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Core\Container;
use Spiral\Core\FactoryInterface;
use Spiral\Queue\Config\QueueConfig;
use Spiral\Queue\ContainerRegistry;
use Spiral\Queue\Core\QueueInjector;
use Spiral\Queue\DefaultSerializer;
use Spiral\Queue\Driver\NullDriver;
use Spiral\Queue\Driver\SyncDriver;
use Spiral\Queue\Failed\FailedJobHandlerInterface;
use Spiral\Queue\Failed\LogFailedJobHandler;
use Spiral\Queue\HandlerRegistryInterface;
use Spiral\Queue\QueueConnectionProviderInterface;
use Spiral\Queue\QueueInterface;
use Spiral\Queue\QueueManager;
use Spiral\Queue\QueueRegistry;
use Spiral\Queue\SerializerInterface;

final class QueueBootloader extends Bootloader
{
    protected const SINGLETONS = [
        HandlerRegistryInterface::class => QueueRegistry::class,
        FailedJobHandlerInterface::class => LogFailedJobHandler::class,
        QueueConnectionProviderInterface::class => QueueManager::class,
        QueueManager::class => [self::class, 'initQueueManager'],
        QueueRegistry::class => [self::class, 'initRegistry'],
    ];

    /** @var ConfiguratorInterface */
    private $config;

    public function __construct(ConfiguratorInterface $config)
    {
        $this->config = $config;
    }

    public function boot(Container $container, EnvironmentInterface $env, AbstractKernel $kernel): void
    {
        $this->initQueueConfig($env);
        $this->registerJobsSerializer($container);
        $this->registerQueue($container);

        $this->registerDriverAlias(SyncDriver::class, 'sync');
        $container->bindInjector(QueueInterface::class, QueueInjector::class);

        $kernel->started(static function () use ($container): void {
            $registry = $container->get(HandlerRegistryInterface::class);
            $config = $container->get(QueueConfig::class);

            foreach ($config->getRegistryHandlers() as $jobType => $handler) {
                $registry->setHandler($jobType, $handler);
            }
        });
    }

    public function registerDriverAlias(string $driverClass, string $alias): void
    {
        $this->config->modify(
            'queue',
            new Append('driverAliases', $alias, $driverClass)
        );
    }

    protected function initQueueManager(FactoryInterface $factory): QueueManager
    {
        return $factory->make(QueueManager::class);
    }

    protected function initRegistry(ContainerInterface $container, ContainerRegistry $registry)
    {
        return new QueueRegistry($container, $registry);
    }

    private function registerJobsSerializer(Container $container): void
    {
        $container->bindSingleton(SerializerInterface::class, static function () {
            return new DefaultSerializer();
        });
    }

    private function registerQueue(Container $container): void
    {
        $container->bindSingleton(
            QueueInterface::class,
            static function (QueueManager $manager): QueueInterface {
                return $manager->getConnection();
            }
        );
    }

    private function initQueueConfig(EnvironmentInterface $env): void
    {
        $this->config->setDefaults(
            QueueConfig::CONFIG,
            [
                'default' => $env->get('QUEUE_CONNECTION', 'sync'),
                'connections' => [
                    'sync' => [
                        'driver' => 'sync',
                    ],
                ],
                'registry' => [
                    'handlers' => [],
                ],
                'driverAliases' => [
                    'sync' => SyncDriver::class,
                    'null' => NullDriver::class,
                ],
            ]
        );
    }
}
