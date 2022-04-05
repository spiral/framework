<?php

declare(strict_types=1);

namespace Spiral\Exceptions;

interface ErrorReporterInterface
{
    public function report(
        \Throwable $exception,
        Verbosity $verbosity = null,
    ): string;
}
