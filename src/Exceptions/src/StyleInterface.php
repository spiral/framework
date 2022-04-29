<?php

declare(strict_types=1);

namespace Spiral\Exceptions;

/**
 * Provides actual styling for a given token or line.
 */
interface StyleInterface
{
    /**
     * Apply render specific colorization to the line.
     */
    public function token(array $token, array $previous): string;

    /**
     * Render specific code line.
     */
    public function line(int $number, string $code, bool $target = false): string;
}
