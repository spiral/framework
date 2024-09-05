<?php

declare(strict_types=1);

namespace Spiral\Core\Internal\Proxy;

use Psr\Container\ContainerInterface;
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
            throw new ContainerException(
                \sprintf('Unable to resolve `%s` in a Proxy in `%s` scope.', $alias, self::getScope($c)),
                previous: $e,
            );
        }

        if (Proxy::isProxy($result)) {
            throw new RecursiveProxyException(
                \sprintf('Recursive proxy detected for `%s` in `%s` scope.', $alias, self::getScope($c)),
            );
        }

        return $result;
    }

    /**
     * @return non-empty-string
     */
    private static function getScope(ContainerInterface $c): string
    {
        return \implode('.', \array_reverse(\array_map(
            static fn (?string $name): string => $name ?? 'null',
            Introspector::scopeNames($c),
        )));
    }
}
