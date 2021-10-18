<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Bootloader\Broadcast;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\RoadRunner\Broadcast\BroadcastInterface;
use Spiral\RoadRunner\Broadcast\Broadcast;

/**
 * Enables support for event/message publishing.
 */
final class BroadcastBootloader extends Bootloader
{
    protected const SINGLETONS = [
        BroadcastInterface::class => Broadcast::class,
        Broadcast::class          => Broadcast::class,
    ];
}
