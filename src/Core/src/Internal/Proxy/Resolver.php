<?php

declare(strict_types=1);

namespace Spiral\Core\Internal\Proxy;

use Spiral\Core\ContainerScope;
use Spiral\Core\Exception\Container\ContainerException;

/**
 * @internal
 */
final class Resolver
{
    public static function resolve(string $alias, \Stringable|string|null $context = null): object
    {
        $c = ContainerScope::getContainer() ?? throw new ContainerException('Proxy is out of scope.');

        try {
            $result = $c->get($alias, $context) ?? throw new ContainerException(
                'Resolved `null` from the container.',
            );
        } catch (ContainerException $e) {
            throw new ContainerException(
                // todo : find required scope
                \sprintf('Unable to resolve `%s` in a Proxy.', $alias),
                previous: $e,
            );
        }

        return $result;
    }
}
