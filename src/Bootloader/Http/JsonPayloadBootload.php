<?php
/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */
declare(strict_types=1);

namespace Spiral\Bootloader\Http;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Http\Middleware\JsonPayloadMiddleware;

final class JsonPayloadBootload extends Bootloader
{
    const DEPENDENCIES = [
        HttpBootloader::class
    ];

    /**
     * @param HttpBootloader $http
     */
    public function boot(HttpBootloader $http)
    {
        $http->addMiddleware(JsonPayloadMiddleware::class);
    }
}