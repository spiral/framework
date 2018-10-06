<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\Bootloader\Dispatcher;

use Spiral\Boot\KernelInterface;
use Spiral\Core\Bootloader\Bootloader;
use Spiral\Jobs\JobDispatcher;
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
     * @param JobDispatcher   $jobs
     */
    public function boot(KernelInterface $kernel, JobDispatcher $jobs)
    {
        $kernel->addDispatcher($jobs);
    }
}