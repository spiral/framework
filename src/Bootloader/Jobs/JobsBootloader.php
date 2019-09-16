<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Bootloader\Jobs;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\KernelInterface;
use Spiral\Bootloader\ServerBootloader;
use Spiral\Jobs\Factory\SpiralFactory;
use Spiral\Jobs\FactoryInterface;
use Spiral\Jobs\JobDispatcher;
use Spiral\Jobs\Queue;
use Spiral\Jobs\QueueInterface;

final class JobsBootloader extends Bootloader
{
    const DEPENDENCIES = [
        ServerBootloader::class
    ];

    public const SINGLETONS = [
        QueueInterface::class   => Queue::class,
        FactoryInterface::class => SpiralFactory::class
    ];

    /**
     * @param KernelInterface $kernel
     * @param JobDispatcher   $jobs
     */
    public function boot(KernelInterface $kernel, JobDispatcher $jobs)
    {
        $kernel->addDispatcher($jobs);
    }
}
