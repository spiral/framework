<?php

declare(strict_types=1);

namespace Spiral\Exceptions\Boot;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\Container;
use Spiral\Exceptions\ErrorHandler;
use Spiral\Exceptions\ErrorHandlerInterface;
use Spiral\Exceptions\ErrorRendererInterface;
use Spiral\Exceptions\ErrorReporterInterface;
use Spiral\Exceptions\Renderer\ConsoleRenderer;
use Spiral\Exceptions\Renderer\HtmlRenderer;
use Spiral\Exceptions\Renderer\JsonRenderer;
use Spiral\Exceptions\Renderer\PlainRenderer;
use Spiral\Exceptions\Verbosity;

/**
 * Declare error handler that contains error renderers and error reporters.
 */
final class ErrorHandlerBootloader extends Bootloader
{
    protected const SINGLETONS = [
        ErrorRendererInterface::class => ErrorHandlerInterface::class,
        ErrorReporterInterface::class => ErrorHandlerInterface::class,
        ErrorHandlerInterface::class => [self::class, 'createHandler'],
    ];

    public function boot(Container $container): void
    {
        $handler = new ErrorHandler();


        $handler->addRenderers(
            // $cli = $container->get(ConsoleRenderer::class),
            $plain = $container->get(PlainRenderer::class),
            $json = $container->get(JsonRenderer::class),
            $html = $container->get(HtmlRenderer::class),
        );

        $container->bindSingleton(ErrorHandlerInterface::class, $handler);
        $container->bindSingleton($handler::class, $handler);
    }
}
