<?php

declare(strict_types=1);

namespace Spiral\Core;

use ReflectionFunctionAbstract as ContextFunction;
use Spiral\Core\Exception\Resolver\InvalidArgumentException;
use Spiral\Core\Exception\Resolver\ResolvingException;

/**
 * Has to resolve arguments (somehow) or die for specified method, function or class constructor.
 */
interface ResolverInterface
{
    /**
     * Get list of arguments with resolved dependencies for specified function or method.
     *
     * @param ContextFunction $reflection Target function or method.
     * @param array $parameters User specified parameters.
     *
     * @throws ResolvingException
     */
    public function resolveArguments(
        ContextFunction $reflection,
        array $parameters = [],
        bool $validate = true,
    ): array;

    /**
     * Validate arguments list to possibility to call reflected function.
     *
     * @throws InvalidArgumentException
     */
    public function validateArguments(ContextFunction $reflection, array $arguments = []): void;
}
