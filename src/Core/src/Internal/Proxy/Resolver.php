<?php

declare(strict_types=1);

namespace Spiral\Core\Internal\Proxy;

use Psr\Container\ContainerInterface;
use Spiral\Core\ContainerScope;
use Spiral\Core\Exception\Container\ContainerException;

/**
 * @internal
 */
final class Resolver
{
    public static function resolve(
        string $alias,
        \Stringable|string|null $context = null,
        ?ContainerInterface $c = null
    ): object {
        $c ??= ContainerScope::getContainer() ?? throw new ContainerException('Proxy is out of scope.');

        try {
            /** @psalm-suppress TooManyArguments */
            $result = $c->get($alias, $context) ?? throw new ContainerException(
                'Resolved `null` from the container.',
            );
        } catch (\Throwable $e) {
            throw new ContainerException(
                \sprintf('Unable to resolve `%s` in a Proxy.', $alias),
                previous: $e,
            );
        }

        return $result;
    }
}
