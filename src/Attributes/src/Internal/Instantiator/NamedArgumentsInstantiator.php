<?php

declare(strict_types=1);

namespace Spiral\Attributes\Internal\Instantiator;

use Spiral\Attributes\Internal\Exception;

/**
 * @internal NamedArgumentsInstantiator is an internal library class, please do not use it in your code.
 * @psalm-internal Spiral\Attributes
 */
final class NamedArgumentsInstantiator extends Instantiator
{
    public function instantiate(\ReflectionClass $attr, array $arguments, \Reflector $context = null): object
    {
        try {
            return $attr->newInstanceArgs($arguments);
        } catch (\Throwable $e) {
            throw Exception::withLocation($e, $attr->getFileName(), $attr->getStartLine());
        }
    }
}
