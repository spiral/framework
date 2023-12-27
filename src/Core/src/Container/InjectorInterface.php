<?php

declare(strict_types=1);

namespace Spiral\Core\Container;

use Spiral\Core\Exception\Container\ContainerException;

/**
 * Magic spiral interface used to resolve dependencies based on their context. Container may
 * execute such method if INJECTOR constant found in requested class.
 *
 * @template TClass of object
 */
interface InjectorInterface
{
    /**
     * Injector will receive requested class or interface reflection and reflection linked
     * to parameter in constructor or method.
     *
     * This method can return pre-defined instance or create new one based on requested class.
     * Parameter reflection can be used for dynamic class constructing, for example it can define
     * database name or config section to be used to construct requested instance.
     *
     * @param \ReflectionClass<TClass> $class Request class type.
     * @param string|null $context Parameter or alias name.
     *
     * @return TClass
     *
     * @throws ContainerException
     */
    public function createInjection(\ReflectionClass $class, ?string $context = null): object;
}
