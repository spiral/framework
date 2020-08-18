<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Exceptions;

/**
 * Provides actual styling for a given token or line.
 */
interface StyleInterface
{
    /**
     * Apply render specific colorization to the line.
     *
     * @param array $token
     * @param array $previous
     * @return string
     */
    public function token(array $token, array $previous): string;

    /**
     * Render specific code line.
     *
     * @param int    $number
     * @param string $code
     * @param bool   $target
     * @return string
     */
    public function line(int $number, string $code, bool $target = false): string;
}
