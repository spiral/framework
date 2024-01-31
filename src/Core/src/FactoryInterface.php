<?php

declare(strict_types=1);

namespace Spiral\Core;

use Spiral\Core\Exception\Container\AutowireException;

/**
 * Declares ability to construct classes.
 */
interface FactoryInterface
{
    /**
     * Create instance of requested class using binding class aliases and set of parameters provided
     * by user, rest of constructor parameters must be filled by container. Method might return
     * pre-constructed singleton when no parameters are specified.
     *
     * @template T
     *
     * @param class-string<T>|string $alias
     * @param array $parameters Parameters to construct new class.
     * @psalm \Stringable|string|null $context Related to parameter caused injection if any.
     *        Will be added in the signature {@since 4.0.0}
     *
     * @return T
     * @psalm-return ($alias is class-string ? T : mixed)
     *
     * @throws AutowireException
     */
    public function make(string $alias, array $parameters = []): mixed;
}
