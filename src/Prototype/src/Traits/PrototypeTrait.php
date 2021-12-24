<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Prototype\Traits;

use Spiral\Core\ContainerScope;
use Spiral\Core\Exception\ScopeException;
use Spiral\Prototype\Exception\PrototypeException;
use Spiral\Prototype\PrototypeRegistry;

/**
 * This DocComment is auto-generated, do not edit or commit this file to repository.
 */
trait PrototypeTrait
{
    /**
     * Automatic resolution of scoped dependency to it's value. Relies
     * on global container scope.
     *
     * @return mixed
     * @throws ScopeException
     */
    public function __get(string $name)
    {
        $container = ContainerScope::getContainer();
        if ($container === null || !$container->has(PrototypeRegistry::class)) {
            throw new ScopeException(
                "Unable to resolve prototyped dependency `{$name}`, invalid container scope"
            );
        }

        /** @var PrototypeRegistry $registry */
        $registry = $container->get(PrototypeRegistry::class);

        $target = $registry->resolveProperty($name);
        if (
            $target === null ||
            $target instanceof \Throwable ||
            $target->type->fullName === null
        ) {
            throw new PrototypeException(
                "Undefined prototype property `{$name}`",
                0,
                $target instanceof \Throwable ? $target : null
            );
        }

        return $container->get($target->type->name());
    }
}
