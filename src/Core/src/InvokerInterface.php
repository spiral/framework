<?php

declare(strict_types=1);

namespace Spiral\Core;

use Spiral\Core\Exception\Container\NotCallableException;

/**
 * Invoke a callable.
 */
interface InvokerInterface
{
    /**
     * Call the given function using the given parameters.
     *
     * @param callable|non-empty-string|array{class-string, non-empty-string} $target
     * @param array<non-empty-string,mixed> $parameters
     * @return mixed
     * @throws NotCallableException
     */
    public function invoke($target, array $parameters = []);
}
