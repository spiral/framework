<?php

declare(strict_types=1);

namespace Spiral\Exceptions\Boot;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\Container;
use Spiral\Core\FactoryInterface;
use Spiral\Debug\StateInterface;
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
        ErrorHandlerInterface::class => ErrorHandler::class,
    ];
    private ErrorHandler $handler;

    public function __construct(
        private readonly FactoryInterface $factory
    ) {
        $this->handler = new ErrorHandler();
    }

    public function boot(Container $container): void
    {
        $container->bindSingleton($this->handler::class, $this->handler);

        $this->addRenderers(
            // $cli = $container->get(ConsoleRenderer::class),
            $html = $container->get(HtmlRenderer::class),
            $json = $container->get(JsonRenderer::class),
            $plain = $container->get(PlainRenderer::class),
        );
        $plain->defaultVerbosity = Verbosity::BASIC;
    }

    /**
     * @param ErrorRendererInterface|class-string<ErrorRendererInterface> ...$renderers
     */
    public function addRenderers(ErrorRendererInterface|string ...$renderers)
    {
        foreach ($renderers as $renderer) {
            if (\is_string($renderer)) {
                $renderer = $this->factory->make($renderer);
            }
            $this->handler->addRenderers($renderer);
        }
    }
}
