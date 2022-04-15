<?php

declare(strict_types=1);

namespace Spiral\Exceptions;

interface ExceptionHandlerInterface extends ExceptionReporterInterface, ExceptionRendererInterface
{
    /**
     * Enable global exception handling.
     */
    public function register(): void;

    /**
     * Handle global exception outside (a Dispatcher) and output error to the user.
     *
     * @internal
     */
    public function handleGlobalException(\Throwable $e): void;

    public function getRenderer(?string $format = null): ?ExceptionRendererInterface;
}
