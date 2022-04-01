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
use Spiral\Broadcast\Broadcast;
use Spiral\Broadcast\BroadcastInterface;

/**
 * Enables support for event/message publishing.
 * @deprecated since v2.12. Will be removed in v3.0
 */
final class BroadcastBootloader extends Bootloader
{
    protected const SINGLETONS = [
        BroadcastInterface::class => Broadcast::class,
        Broadcast::class          => Broadcast::class,
    ];
}
