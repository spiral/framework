<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Boot\Exception\BootException;
use Spiral\Goridge\RPC;
use Spiral\Goridge\SocketRelay;

/**
 * Configures RPC connection to upper RoadRunner server.
 */
final class RpcBootloader extends Bootloader
{
    const RPC_DEFAULT = 'tcp://127.0.0.1:6001';
    const SINGLETONS  = [
        RPC::class => [self::class, 'rpc']
    ];

    /**
     * @param EnvironmentInterface $env
     * @return RPC
     */
    protected function rpc(EnvironmentInterface $env): RPC
    {
        $conn = $env->get('RR_RPC', static::RPC_DEFAULT);

        if (!preg_match('#^([a-z]+)://([^:]+):?(\d+)?$#i', $conn, $parts)) {
            throw new BootException(
                "Unable to configure RPC connection, invalid DSN given `{$conn}`."
            );
        }

        if (!in_array($parts[1], ['tcp', 'unix'])) {
            throw new BootException(
                "Unable to configure RPC connection, invalid DSN given `{$conn}`."
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