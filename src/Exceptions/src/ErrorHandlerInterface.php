<?php

declare(strict_types=1);

namespace Spiral\Exceptions;

use Spiral\Http\Middleware\ErrorRendererInterface;
use Spiral\Http\Middleware\ErrorReporterInterface;

interface ErrorHandlerInterface extends ErrorReporterInterface, ErrorRendererInterface
{
    public function getRenderer(?string $format = null): ?ErrorRendererInterface;

    public function shouldReport(\Throwable $exception): bool;
}
