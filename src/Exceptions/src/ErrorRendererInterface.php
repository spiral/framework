<?php

declare(strict_types=1);

namespace Spiral\Exceptions;

use JetBrains\PhpStorm\ExpectedValues;

interface ErrorRendererInterface
{
    public function render(
        \Throwable $exception,

        #[ExpectedValues(valuesFromClass: HandlerInterface::class)]
        ?int $verbosity = HandlerInterface::VERBOSITY_BASIC,

        /** Preferred format */
        string $format = null,
    ): string;

    public function canRender(string $format): bool;
}
