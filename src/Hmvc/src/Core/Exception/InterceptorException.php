<?php

declare(strict_types=1);

namespace Spiral\Core\Exception;

// Load the original class to make an alias
if (!\class_exists(\Spiral\Interceptors\Exception\InterceptorException::class)) {
    /**
     * @deprecated will be removed in Spiral v4.0
     * Use {@see \Spiral\Interceptors\Exception\InterceptorException} instead.
     */
    class InterceptorException extends \RuntimeException
    {
    }
}
