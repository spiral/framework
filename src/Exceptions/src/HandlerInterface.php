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
 * HandlerInterface is responsible for an exception explanation.
 */
interface HandlerInterface
{
    /**
     * Verbosity levels for stack trace.
     */
    public const VERBOSITY_BASIC   = 0;
    public const VERBOSITY_VERBOSE = 1;
    public const VERBOSITY_DEBUG   = 2;

    /**
     * Method must return prepared exception message.
     *
     * @param \Throwable $e
     * @return string
     */
    public function getMessage(\Throwable $e): string;

    /**
     * Render exception debug information into stream.
     *
     * @param \Throwable $e
     * @param int        $verbosity
     * @return string
     */
    public function renderException(\Throwable $e, int $verbosity = self::VERBOSITY_VERBOSE): string;
}
