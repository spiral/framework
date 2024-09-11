<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

use Psr\Container\ContainerInterface;
use Spiral\Core\Container;
use Spiral\Core\ContainerScope;
use Spiral\Core\Internal\Introspector\Accessor;

/**
 * @internal
 */
final class Introspector
{
    public static function scopeName(?ContainerInterface $container = null): ?string
    {
        return self::getAccessor($container)->scope->getScopeName();
    }

    /**
     * Returns list of scope names starting from the current scope to the root scope.
     *
     * @return list<string|null>
     */
    public static function scopeNames(?ContainerInterface $container = null): array
    {
        $scope = self::getAccessor($container)->scope;
        $result = [];
        do {
            $result[] = $scope->getScopeName();
            $scope = $scope->getParentScope();
        } while ($scope !== null);

        return $result;
    }

    /**
     * @psalm-assert Container|null $container
     */
    public static function getAccessor(?ContainerInterface $container = null): Accessor
    {
        $container = match (true) {
            $container === null || $container instanceof Container => $container,
            Proxy::isProxy($container) => ContainerScope::getContainer() ?? throw new \RuntimeException(
                'Container Proxy is out of scope.',
            ),
            default => throw new \InvalidArgumentException('Container must be an instance of ' . Container::class),
        };

        $container ??= ContainerScope::getContainer();

        if (!$container instanceof Container) {
            throw new \RuntimeException('Container is not available.');
        }

        return new Accessor($container);
    }
}
