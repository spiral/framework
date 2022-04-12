<?php

declare(strict_types=1);

namespace Spiral\Bootloader;

use Closure;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\Container;
use Spiral\Core\FactoryInterface;
use Spiral\Exceptions\ErrorHandler;
use Spiral\Exceptions\ErrorHandlerInterface;
use Spiral\Exceptions\ErrorRendererInterface;
use Spiral\Exceptions\ErrorReporterInterface;
use Spiral\Exceptions\Renderer\ConsoleRenderer;
use Spiral\Exceptions\Renderer\HtmlRenderer;
use Spiral\Exceptions\Renderer\JsonRenderer;
use Spiral\Exceptions\Renderer\PlainRenderer;
use Spiral\Exceptions\Reporter\SnapshotterReporter;

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

        $this->addRenderer($container->get(PlainRenderer::class));
        $this->addRenderer($container->get(ConsoleRenderer::class));

        $this->addRenderer($container->get(HtmlRenderer::class));
        $this->addRenderer($container->get(JsonRenderer::class));

        $this->addReporter(
            function (\Throwable $exception) {
                $this->factory->make(SnapshotterReporter::class)->report($exception);
            }
        );
    }

    /**
     * @param ErrorRendererInterface|class-string<ErrorRendererInterface> $renderer
     */
    public function addRenderer(ErrorRendererInterface|string $renderer): void
    {
        if (\is_string($renderer)) {
            $renderer = $this->factory->make($renderer);
        }
        \assert($renderer instanceof ErrorRendererInterface);
        $this->handler->addRenderer($renderer);
    }

    /**
     * @param ErrorReporterInterface|Closure(\Throwable):void|class-string<ErrorReporterInterface> $reporter
     */
    public function addReporter(ErrorReporterInterface|Closure|string $reporter): void
    {
        if (\is_string($reporter)) {
            $reporter = $this->factory->make($reporter);
        }
        $this->handler->addReporters($reporter);
    }
}
