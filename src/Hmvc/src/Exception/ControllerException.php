<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Core\Exception;

/**
 * Unable to perform user action or find controller.
 */
class ControllerException extends RuntimeException
{
    /**
     * Pre-defined controller error codes.
     */
    public const NOT_FOUND    = 0;
    public const BAD_ACTION   = 1;
    public const BAD_ARGUMENT = 2;
    public const FORBIDDEN    = 3;
    public const ERROR        = 4;
    public const UNAUTHORIZED = 8;
}
