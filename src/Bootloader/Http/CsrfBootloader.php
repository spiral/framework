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
use Spiral\Boot\Bootloader\DependedInterface;
use Spiral\Http\Middleware\CsrfMiddleware;

final class CsrfBootloader extends Bootloader implements DependedInterface
{
    /**
     * @param HttpBootloader $http
     */
    public function boot(HttpBootloader $http)
    {
        $http->addMiddleware(CsrfMiddleware::class);
    }

    /**
     * @return array
     */
    public function defineDependencies(): array
    {
        return [
            HttpBootloader::class
        ];
    }
}