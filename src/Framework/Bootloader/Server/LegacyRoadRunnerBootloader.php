<?php

/**
 * This file is part of Spiral Framework package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spiral\Bootloader\Server;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Boot\Exception\BootException;
use Spiral\Core\Container;
use Spiral\Goridge\RPC;
use Spiral\Goridge\SocketRelay;
use Spiral\Goridge\StreamRelay;
use Spiral\RoadRunner\Metrics;
use Spiral\RoadRunner\MetricsInterface;
use Spiral\RoadRunner\Worker;

/**
 * @deprecated since v2.9. Will be moved to spiral/roadrunner-bridge and removed in v3.0
 */
class LegacyRoadRunnerBootloader extends Bootloader
{
    /**
     * @var string
     */
    private const RPC_DEFAULT = 'tcp://127.0.0.1:6001';

    /**
     * @var string
     */
    private const WORKER_DEFAULT = 'pipes';

    /**
     * @param Container $container
     */
    public function boot(Container $container)
    {
        $container->bindSingleton(RPC::class, function (EnvironmentInterface $env) {
            return $this->rpc($env);
        });

        $container->bindSingleton(Worker::class, function (EnvironmentInterface $env) {
            return $this->worker($env);
        });

        $container->bindSingleton(MetricsInterface::class, function (RPC $rpc) {
            return new Metrics($rpc);
        });
    }

    /**
     * @param EnvironmentInterface $env
     * @return RPC
     */
    protected function rpc(EnvironmentInterface $env): RPC
    {
        $conn = $env->get('RR_RPC', self::RPC_DEFAULT);

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
            $relay = new SocketRelay($parts[2], (int)$parts[3], SocketRelay::SOCK_TCP);
        }

        return new RPC($relay);
    }

    /**
     * @param EnvironmentInterface $env
     * @return Worker
     */
    protected function worker(EnvironmentInterface $env): Worker
    {
        $conn = $env->get('RR_RELAY', self::WORKER_DEFAULT);

        if ($conn === 'pipes' || empty($conn)) {
            return new Worker(new StreamRelay(STDIN, STDOUT));
        }

        if (!preg_match('#^([a-z]+)://([^:]+):?(\d+)?$#i', $conn, $parts)) {
            throw new BootException(
                "Unable to configure Worker connection, invalid DSN given `{$conn}`."
            );
        }

        if (!in_array($parts[1], ['tcp', 'unix'])) {
            throw new BootException(
                "Unable to configure Worker connection, invalid DSN given `{$conn}`."
            );
        }

        if ($parts[1] == 'unix') {
            $relay = new SocketRelay($parts[2], null, SocketRelay::SOCK_UNIX);
        } else {
            $relay = new SocketRelay($parts[2], (int)$parts[3], SocketRelay::SOCK_TCP);
        }

        return new Worker($relay);
    }
}
