<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Bootloader\Jobs;

use Psr\Container\ContainerInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\KernelInterface;
use Spiral\Bootloader\ServerBootloader;
use Spiral\Core\Container;
use Spiral\Goridge\RPC as LegacyRPC;
use Spiral\Goridge\RPC\RPC;
use Spiral\Jobs\HandlerRegistryInterface;
use Spiral\Jobs\JobDispatcher;
use Spiral\Jobs\JobQueue;
use Spiral\Jobs\JobRegistry;
use Spiral\Jobs\QueueInterface;
use Spiral\Jobs\Registry\ContainerRegistry;
use Spiral\Jobs\SerializerRegistryInterface;

/**
 * @deprecated since v2.9. Will be moved to spiral/roadrunner-bridge and removed in v3.0
 */
final class JobsBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        ServerBootloader::class,
    ];

    protected const SINGLETONS = [
        HandlerRegistryInterface::class    => JobRegistry::class,
        SerializerRegistryInterface::class => JobRegistry::class,
        JobRegistry::class                 => [self::class, 'jobRegistry'],
    ];

    /**
     * @param Container $container
     * @param KernelInterface $kernel
     * @param JobDispatcher $jobs
     */
    public function boot(Container $container, KernelInterface $kernel, JobDispatcher $jobs): void
    {
        $kernel->addDispatcher($jobs);

        if (\class_exists(LegacyRPC::class)) {
            $container->bindSingleton(QueueInterface::class, function (LegacyRPC $rpc, JobRegistry $registry) {
                return new JobQueue($rpc, $registry);
            });
        } else {
            $container->bindSingleton(QueueInterface::class, function (RPC $rpc, JobRegistry $registry) {
                return new JobQueue($rpc, $registry);
            });
        }
    }

    /**
     * @param ContainerInterface $container
     * @param ContainerRegistry $registry
     * @return JobRegistry
     */
    private function jobRegistry(ContainerInterface $container, ContainerRegistry $registry)
    {
        return new JobRegistry($container, $registry, $registry);
    }
}
