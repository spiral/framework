<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Bootloader;

use Spiral\Boot\KernelInterface;
use Spiral\Core\Bootloader\Bootloader;
use Spiral\Jobs\JobsDispatcher;
use Spiral\Jobs\Queue;
use Spiral\Jobs\QueueInterface;

class JobsBootloader extends Bootloader
{
    const BOOT = true;

    const SINGLETONS = [
        QueueInterface::class => Queue::class,
    ];

    /**
     * @param KernelInterface $kernel
     * @param JobsDispatcher  $jobs
     */
    public function boot(KernelInterface $kernel, JobsDispatcher $jobs)
    {
        $kernel->addDispatcher($jobs);
    }
}