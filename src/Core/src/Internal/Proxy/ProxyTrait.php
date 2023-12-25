<?php

declare(strict_types=1);

namespace Spiral\Core\Internal\Proxy;

use Spiral\Core\ContainerScope;
use Spiral\Core\Exception\Container\ContainerException;

trait ProxyTrait
{
    public static string $__container_proxy_alias;

    public function __call(string $name, array $arguments)
    {
        return self::resolve(static::$__container_proxy_alias)->$name(...$arguments);
    }

    public static function __callStatic(string $name, array $arguments)
    {
        return self::resolve(static::$__container_proxy_alias)->$name(...$arguments);
    }

    private static function resolve(string $alias): object
    {
        $c = ContainerScope::getContainer() ?? throw new ContainerException('Proxy is out of scope.');

        try {
            $result = $c->get($alias) ?? throw new ContainerException(
                'Resolved `null` from the container.',
            );
        } catch (ContainerException $e) {
            throw new ContainerException(
                // todo : find required scope
                \sprintf('Unable to resolve `%s` in a Proxy.', static::$__container_proxy_alias),
                previous: $e,
            );
        }

        return $result;
    }
}
