<?php

declare(strict_types=1);

namespace Spiral\Core\Internal\Proxy;

/**
 * @internal
 */
trait MagicCallTrait
{
    public function __call(string $name, array $arguments)
    {
        return Resolver::resolve(static::$__container_proxy_alias, $this->__container_proxy_context)
            ->$name(...$arguments);
    }

    public static function __callStatic(string $name, array $arguments)
    {
        return Resolver::resolve(static::$__container_proxy_alias)
            ->$name(...$arguments);
    }
}
