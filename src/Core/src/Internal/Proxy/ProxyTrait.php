<?php

declare(strict_types=1);

namespace Spiral\Core\Internal\Proxy;

use Spiral\Core\ContainerScope;
use Spiral\Core\Exception\Container\ContainerException;

/**
 * @internal
 */
trait ProxyTrait
{
    public static string $__container_proxy_alias;

    public function __call(string $name, array $arguments)
    {
        return Resolver::resolve(static::$__container_proxy_alias)->$name(...$arguments);
    }

    public static function __callStatic(string $name, array $arguments)
    {
        return Resolver::resolve(static::$__container_proxy_alias)->$name(...$arguments);
    }
}
