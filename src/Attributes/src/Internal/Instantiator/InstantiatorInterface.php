<?php

declare(strict_types=1);

namespace Spiral\Attributes\Internal\Instantiator;

/**
 * @internal InstantiatorInterface is an internal library interface, please do not use it in your code.
 * @psalm-internal Spiral\Attributes
 *
 * @psalm-type ContextType = \ReflectionClass
 *                         | \ReflectionFunctionAbstract
 *                         | \ReflectionProperty
 *                         | \ReflectionClassConstant
 *                         | \ReflectionParameter
 */
interface InstantiatorInterface
{
    /**
     * @template T of object
     *
     * @param \ReflectionClass<T> $attr
     * @param array<positive-int|0|string, mixed> $arguments
     * @param ContextType $context
     * @return T
     * @throws \Throwable
     */
    public function instantiate(\ReflectionClass $attr, array $arguments, \Reflector $context = null): object;
}
