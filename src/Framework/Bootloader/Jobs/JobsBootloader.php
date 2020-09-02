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
use Spiral\Jobs\HandlerRegistryInterface;
use Spiral\Jobs\JobDispatcher;
use Spiral\Jobs\JobQueue;
use Spiral\Jobs\JobRegistry;
use Spiral\Jobs\QueueInterface;
use Spiral\Jobs\Registry\ContainerRegistry;
use Spiral\Jobs\SerializerRegistryInterface;

final class JobsBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        ServerBootloader::class,
    ];

    protected const SINGLETONS = [
        QueueInterface::class              => JobQueue::class,
        HandlerRegistryInterface::class    => JobRegistry::class,
        SerializerRegistryInterface::class => JobRegistry::class,
        JobRegistry::class                 => [self::class, 'jobRegistry'],
    ];

    /**
     * @param KernelInterface $kernel
     * @param JobDispatcher $jobs
     */
    public function boot(KernelInterface $kernel, JobDispatcher $jobs): void
    {
        $kernel->addDispatcher($jobs);
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
