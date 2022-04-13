<?php

declare(strict_types=1);

namespace Spiral\Exceptions;

interface ExceptionRendererInterface
{
    /**
     * @param string|null $format Preferred format
     */
    public function render(
        \Throwable $exception,
        ?Verbosity $verbosity = Verbosity::BASIC,
        string $format = null,
    ): string;

    public function canRender(string $format): bool;
}
