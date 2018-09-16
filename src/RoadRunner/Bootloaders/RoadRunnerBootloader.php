<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\RoadRunner\Bootloaders;

use Spiral\Boot\EnvironmentInterface;
use Spiral\Boot\KernelInterface;
use Spiral\Core\Bootloaders\Bootloader;
use Spiral\Goridge\RPC;
use Spiral\Goridge\SocketRelay;
use Spiral\RoadRunner\RoadRunnerDispatcher;

class RoadRunnerBootloader extends Bootloader
{
    const BOOT = true;

    const SINGLETONS = [
        RoadRunnerDispatcher::class => RoadRunnerDispatcher::class,
        RPC::class                  => [self::class, 'rpc']
    ];

    protected function rpc(EnvironmentInterface $environment): RPC
    {
        $conn = $environment->get('RR_RPC', 'tcp://localhost:6001');

        // todo: parse conn


        $relay = new SocketRelay("127.0.0.1", 6001);

        return new RPC($relay);
    }

    /**
     * @param KernelInterface      $kernel
     * @param RoadRunnerDispatcher $rr
     */
    public function boot(KernelInterface $kernel, RoadRunnerDispatcher $rr)
    {
        $kernel->addDispatcher($rr);

        if (function_exists('gc_collect_cycles')) {
            $rr->addFinalizer('gc_collect_cycles');
        }
    }
}