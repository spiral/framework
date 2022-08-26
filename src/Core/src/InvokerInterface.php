<?php

declare(strict_types=1);

namespace Spiral\Core;

use Spiral\Core\Exception\Container\NotCallableException;

/**
 * Invoke a callable.
 *
 * @psalm-type TInvokable = callable|non-empty-string|array{class-string, non-empty-string}
 */
interface InvokerInterface
{
    /**
     * Call the given function using the given parameters.
     *
     * @param callable|non-empty-string|array{class-string, non-empty-string} $target
     *        string - class name or container definition
     *        array - lazy callable where first element can be class name or container definition
     * @param array<non-empty-string, mixed> $parameters Predefined named arguments
     *
     * @throws NotCallableException
     */
    public function invoke(array|callable|string $target, array $parameters = []): mixed;
}
