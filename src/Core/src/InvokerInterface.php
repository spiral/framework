<?php

declare(strict_types=1);

namespace Spiral\Core;

/**
 * Invoke a callable.
 */
interface InvokerInterface
{
    /**
     * Call the given function using the given parameters.
     *
     * @param callable $target
     * @param array $parameters
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function call(callable $target, array $parameters = []);
}
