<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Bootloader\GRPC;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\KernelInterface;
use Spiral\GRPC\GRPCDispatcher;
use Spiral\GRPC\Invoker;
use Spiral\GRPC\InvokerInterface;
use Spiral\GRPC\LocatorInterface;
use Spiral\GRPC\ServiceLocator;

final class GRPCBootloader extends Bootloader
{
    protected const SINGLETONS = [
        InvokerInterface::class => Invoker::class,
        LocatorInterface::class => ServiceLocator::class
    ];

    /**
     * @param KernelInterface $kernel
     * @param GRPCDispatcher  $grpc
     */
    public function boot(KernelInterface $kernel, GRPCDispatcher $grpc)
    {
        $kernel->addDispatcher($grpc);
    }
}
