<?php

declare(strict_types=1);

namespace Spiral\Core\Exception;

// Load the original class to make an alias
use Spiral\Interceptors\Exception\TargetCallException;

if (!\class_exists(TargetCallException::class)) {
    /**
     * Unable to perform user action or find controller.
     *
     * @deprecated will be removed in Spiral v4.0
     */
    class ControllerException extends RuntimeException
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
    }
}
