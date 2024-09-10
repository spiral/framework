<?php

declare(strict_types=1);

namespace Spiral\Core\Internal\Proxy;

use Psr\Container\ContainerInterface;
use Spiral\Core\Container;
use Spiral\Core\ContainerScope;
use Spiral\Core\Exception\Container\ContainerException;
use Spiral\Core\Exception\Container\RecursiveProxyException;
use Spiral\Core\Internal\Introspector;
use Spiral\Core\Internal\Proxy;

/**
 * @internal
 */
final class Resolver
{
    public static function resolve(
        string $alias,
        \Stringable|string|null $context = null,
        ?ContainerInterface $c = null,
    ): object {
        $c ??= ContainerScope::getContainer() ?? throw new ContainerException('Proxy is out of scope.');

        try {
            /** @psalm-suppress TooManyArguments */
            $result = $c->get($alias, $context) ?? throw new ContainerException(
                'Resolved `null` from the container.',
            );
        } catch (\Throwable $e) {
            $scope = self::getScope($c);
            throw new ContainerException(
                $scope === null
                    ? "Unable to resolve `{$alias}` in a Proxy."
                    : "Unable to resolve `{$alias}` in a Proxy in `{$scope}` scope.",
                previous: $e,
            );
        }

        if (Proxy::isProxy($result)) {
            $scope = self::getScope($c);
            throw new RecursiveProxyException(
                $scope === null
                    ? "Recursive proxy detected for `{$alias}`."
                    : "Recursive proxy detected for `{$alias}` in `{$scope}` scope.",
            );
        }

        return $result;
    }

    /**
     * @return non-empty-string|null
     */
    private static function getScope(ContainerInterface $c): ?string
    {
        if (!$c instanceof Container) {
            if (!Proxy::isProxy($c)) {
                return null;
            }

            $c = null;
        }

        return \implode('.', \array_reverse(\array_map(
            static fn (?string $name): string => $name ?? 'null',
            Introspector::scopeNames($c),
        )));
    }
}
