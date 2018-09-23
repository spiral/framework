<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Spiral\RoadRunner\Bootloaders;

use Spiral\Boot\EnvironmentInterface;
use Spiral\Core\Bootloaders\Bootloader;
use Spiral\Goridge\RPC;
use Spiral\Goridge\SocketRelay;

class RoadRunnerBootloader extends Bootloader
{
    const SINGLETONS = [
        RPC::class => [self::class, 'rpc']
    ];

    protected function rpc(EnvironmentInterface $environment): RPC
    {
        $relay = new SocketRelay("127.0.0.1:6001");

        return new RPC($relay);
    }
}