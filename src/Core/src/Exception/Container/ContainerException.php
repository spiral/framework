<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Container;

use Psr\Container\ContainerExceptionInterface;
use Spiral\Core\Exception\RuntimeException;

/**
 * Something inside container.
 */
class ContainerException extends RuntimeException implements ContainerExceptionInterface
{
}
