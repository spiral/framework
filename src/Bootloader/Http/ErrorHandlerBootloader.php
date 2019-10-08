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
use Spiral\Boot\EnvironmentInterface;
use Spiral\Bootloader\SnapshotsBootloader;
use Spiral\Core\Container\Autowire;
use Spiral\Http\ErrorHandler;
use Spiral\Http\Middleware\ErrorHandlerMiddleware;

/**
 * Enable support for HTTP error pages.
 */
final class ErrorHandlerBootloader extends Bootloader
{
    const DEPENDENCIES = [
        SnapshotsBootloader::class,
        HttpBootloader::class
    ];

    const BINDINGS = [
        ErrorHandler\RendererInterface::class => ErrorHandler\PlainRenderer::class,
    ];

    /**
     * @param HttpBootloader       $http
     * @param EnvironmentInterface $env
     */
    public function boot(HttpBootloader $http, EnvironmentInterface $env)
    {
        $http->addMiddleware(new Autowire(
            ErrorHandlerMiddleware::class,
            ['suppressErrors' => !$env->get('DEBUG', false)]
        ));
    }
}
