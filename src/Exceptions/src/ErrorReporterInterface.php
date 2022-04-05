<?php

declare(strict_types=1);

namespace Spiral\Exceptions;

use JetBrains\PhpStorm\ExpectedValues;

interface ErrorReporterInterface
{
    public function report(
        \Throwable $exception,

        #[ExpectedValues(valuesFromClass: HandlerInterface::class)]
        int $verbosity = null,
    ): string;
}
