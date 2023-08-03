<?php

declare(strict_types=1);

namespace Spiral\Core\Exception\Binder;

use Spiral\Core\Exception\ConfiguratorException;

/**
 * Thrown when trying to change a singleton binding that has already been requested.
 * If it is needed in some cases, need to call the {@see \Spiral\Core\Container::removeBinding()} method before binding.
 */
final class SingletonOverloadException extends ConfiguratorException
{
}
