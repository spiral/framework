<?php

declare(strict_types=1);

namespace Spiral\Bootloader;

use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\FactoryInterface;
use Spiral\Exceptions\ExceptionHandler;
use Spiral\Exceptions\Renderer\ConsoleRenderer;
use Spiral\Exceptions\Renderer\JsonRenderer;
use Spiral\Exceptions\Reporter\LoggerReporter;
use Spiral\Exceptions\Reporter\SnapshotterReporter;

/**
 * Adds JSON, HTML and console renderers, adds SnapshotterReporter.
 */
final class ExceptionHandlerBootloader extends Bootloader
{
    public function init(FactoryInterface $factory, ExceptionHandler $errorHandler): void
    {
        $errorHandler->addRenderer(new ConsoleRenderer());
        $errorHandler->addRenderer(new JsonRenderer());

        $errorHandler->addReporter(
            $factory->make(SnapshotterReporter::class)
        );
        $errorHandler->addReporter(
            $factory->make(LoggerReporter::class)
        );
    }
}
