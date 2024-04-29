<?php

declare(strict_types=1);

namespace Spiral\Interceptors\Exception;

use Spiral\Core\Exception\ControllerException;

/**
 * Unable to perform user action or find controller.
 */
class TargetCallException extends \RuntimeException
{
    /**
     * Pre-defined controller error codes.
     */
    public const NOT_FOUND = 0;
    public const BAD_ACTION = 1;
    public const BAD_ARGUMENT = 2;
    public const FORBIDDEN = 3;
    public const ERROR = 4;
    public const UNAUTHORIZED = 8;
    public const INVALID_CONTROLLER = 16;
}

\class_alias(TargetCallException::class, ControllerException::class);
