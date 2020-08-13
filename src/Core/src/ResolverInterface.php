<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Core;

use ReflectionFunctionAbstract as ContextFunction;
use Spiral\Core\Exception\Container\ArgumentException;

/**
 * Has to resolve arguments (somehow) or die for specified method, function or class constructor.
 */
interface ResolverInterface
{
    /**
     * Get list of arguments with resolved dependencies for specified function or method.
     *
     * @param ContextFunction $reflection Target function or method.
     * @param array           $parameters User specified parameters.
     * @return array
     *
     * @throws ArgumentException
     */
    public function resolveArguments(ContextFunction $reflection, array $parameters = []): array;
}
