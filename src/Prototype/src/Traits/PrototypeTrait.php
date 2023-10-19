<?php

declare(strict_types=1);

namespace Spiral\Prototype\Traits;

use Spiral\Core\ContainerScope;
use Spiral\Core\Exception\ScopeException;
use Spiral\Prototype\Exception\PrototypeException;
use Spiral\Prototype\PrototypeRegistry;

trait PrototypeTrait
{
    /**
     * Automatic resolution of scoped dependency to it's value. Relies
     * on global container scope.
     *
     * @throws ScopeException
     */
    public function __get(string $name): mixed
    {
        $container = ContainerScope::getContainer();
        if ($container === null || !$container->has(PrototypeRegistry::class)) {
            throw new ScopeException(
                \sprintf('Unable to resolve prototyped dependency `%s`, invalid container scope', $name)
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
                \sprintf('Undefined prototype property `%s`', $name),
                0,
                $target instanceof \Throwable ? $target : null
            );
        }

        return $container->get($target->type->name());
    }
}
