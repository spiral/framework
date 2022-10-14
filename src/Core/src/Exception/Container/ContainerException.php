<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Container;

use Psr\Container\ContainerExceptionInterface;
use Spiral\Core\Exception\RuntimeException;
use Spiral\Core\Internal\Tracer;

/**
 * Something inside container.
 */
class ContainerException extends RuntimeException implements ContainerExceptionInterface
{
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        protected ?Tracer $tracer = null
    ) {
        parent::__construct($tracer !== null ? $message . PHP_EOL . $tracer : $message, $code, $previous);
    }
}
