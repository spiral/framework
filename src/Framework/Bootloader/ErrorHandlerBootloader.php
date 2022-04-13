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
use Spiral\Exceptions\Renderer\HtmlRenderer;
use Spiral\Exceptions\Renderer\JsonRenderer;
use Spiral\Exceptions\Reporter\SnapshotterReporter;

/**
 * Adds JSON and HTML renderers, adds SnapshotterReporter.
 */
final class ErrorHandlerBootloader extends Bootloader
{

    public function boot(FactoryInterface $factory, ErrorHandler $errorHandler): void
    {
        $errorHandler->addRenderer($factory->make(JsonRenderer::class));
        $errorHandler->addRenderer($factory->make(HtmlRenderer::class));

        $errorHandler->addReporter(
            static function (\Throwable $exception) use ($factory) {
                $factory->make(SnapshotterReporter::class)->report($exception);
            }
        );
    }
}
