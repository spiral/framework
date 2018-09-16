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
use Spiral\RoadRunner\Exceptions\RoadRunnerException;
use Spiral\RoadRunner\RoadRunnerDispatcher;

class RoadRunnerBootloader extends Bootloader
{
    const BOOT        = true;
    const RPC_DEFAULT = 'tcp://127.0.0.1:6001';
    const SINGLETONS  = [
        RoadRunnerDispatcher::class => RoadRunnerDispatcher::class,
        RPC::class                  => [self::class, 'rpc']
    ];

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

    /**
     * @param EnvironmentInterface $environment
     * @return RPC
     */
    protected function rpc(EnvironmentInterface $environment): RPC
    {
        $conn = $environment->get('RR_RPC', static::RPC_DEFAULT);

        if (!preg_match('#^([a-z]+)://([^:]+):?(\d+)?$#i', $conn, $parts)) {
            throw new RoadRunnerException(
                "Unable to create RPC connection, invalid DSN given `{$conn}`."
            );
        }

        if (!in_array($parts[1], ['tcp', 'unix'])) {
            throw new RoadRunnerException(
                "Unable to create RPC connection, invalid DSN given `{$conn}`."
            );
        }

        if ($parts[1] == 'unix') {
            $relay = new SocketRelay($parts[2], null, SocketRelay::SOCK_UNIX);
        } else {
            $relay = new SocketRelay($parts[2], $parts[3], SocketRelay::SOCK_TCP);
        }

        return new RPC($relay);
    }
}