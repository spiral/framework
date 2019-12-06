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
use Spiral\Bootloader\Http\HttpBootloader;

/**
 * Authorizes websocket and server connections using interceptor middleware.
 */
final class WebsocketBootloader extends Bootloader
{
    protected const DEPENDENCIES = [
        BroadcastBootloader::class,
        HttpBootloader::class
    ];
}