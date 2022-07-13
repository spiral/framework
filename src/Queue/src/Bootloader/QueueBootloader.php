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
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\FactoryInterface;
use Spiral\Core\InterceptableCore;
use Spiral\Queue\Config\QueueConfig;
use Spiral\Queue\ContainerRegistry;
use Spiral\Queue\Core\QueueInjector;
use Spiral\Queue\Driver\NullDriver;
use Spiral\Queue\Driver\SyncDriver;
use Spiral\Queue\Failed\FailedJobHandlerInterface;
use Spiral\Queue\Failed\LogFailedJobHandler;
use Spiral\Queue\HandlerRegistryInterface;
use Spiral\Queue\Interceptor\ErrorHandlerInterceptor;
use Spiral\Queue\Interceptor\Handler;
use Spiral\Queue\Interceptor\Core;
use Spiral\Queue\QueueConnectionProviderInterface;
use Spiral\Queue\QueueInterface;
use Spiral\Queue\QueueManager;
use Spiral\Queue\QueueRegistry;

final class QueueBootloader extends Bootloader
{
    protected const SINGLETONS = [
        HandlerRegistryInterface::class => QueueRegistry::class,
        FailedJobHandlerInterface::class => LogFailedJobHandler::class,
        QueueConnectionProviderInterface::class => QueueManager::class,
        QueueManager::class => [self::class, 'initQueueManager'],
        QueueRegistry::class => [self::class, 'initRegistry'],
        Handler::class => [self::class, 'initHandler']
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config
    ) {
    }

    public function init(Container $container, EnvironmentInterface $env, AbstractKernel $kernel): void
    {
        $this->initQueueConfig($env);
        $this->registerQueue($container);

        $this->registerDriverAlias(SyncDriver::class, 'sync');
        $container->bindInjector(QueueInterface::class, QueueInjector::class);

        $kernel->booted(static function () use ($container): void {
            $registry = $container->get(HandlerRegistryInterface::class);
            $config = $container->get(QueueConfig::class);

            foreach ($config->getRegistryHandlers() as $jobType => $handler) {
                $registry->setHandler($jobType, $handler);
            }
        });
    }

    /**
     * @param class-string<CoreInterceptorInterface>|string $interceptor
     */
    public function addInterceptor(string $interceptor): void
    {
        $this->config->modify(
            QueueConfig::CONFIG,
            new Append('interceptors', null, $interceptor)
        );
    }

    public function registerDriverAlias(string $driverClass, string $alias): void
    {
        $this->config->modify(
            QueueConfig::CONFIG,
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

    private function initHandler(Core $core, QueueConfig $config, Container $container): Handler
    {
        $core = new InterceptableCore($core);

        foreach ($config->getInterceptors() as $interceptor) {
            if (\is_string($interceptor)) {
                $interceptor = $container->get($interceptor);
            }

            $core->addInterceptor($interceptor);
        }

        return new Handler($core);
    }

    private function registerQueue(Container $container): void
    {
        $container->bindSingleton(
            QueueInterface::class,
            static fn (QueueManager $manager): QueueInterface => $manager->getConnection()
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
                'interceptors' => [
                    ErrorHandlerInterceptor::class,
                ],
            ]
        );
    }
}
