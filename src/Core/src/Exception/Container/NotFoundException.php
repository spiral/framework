<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Container;

use Psr\Container\NotFoundExceptionInterface;

/**
 * Caused when container is not able to find the proper binding.
 */
class NotFoundException extends AutowireException implements NotFoundExceptionInterface
{
}
