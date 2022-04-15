<?php

declare(strict_types=1);

namespace Spiral\Exceptions;

interface ExceptionReporterInterface
{
    public function report(
        \Throwable $exception,
    ): void;
}
