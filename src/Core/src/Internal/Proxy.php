<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

use Spiral\Core\Internal\Proxy\ProxyClassRenderer;

/**
 * @internal
 */
final class Proxy
{
    /** @var array<class-string, object> */
    private static array $cache = [];

    /**
     * @template TClass of object
     * @param \ReflectionClass<TClass> $type
     * @return TClass
     */
    public static function create(\ReflectionClass $type): object
    {
        $interface = $type->getName();

        if (\array_key_exists($interface, self::$cache)) {
            return self::$cache[$interface];
        }

        $className = "{$type->getNamespaceName()}\\{$type->getShortName()} SCOPED PROXY";

        $classString = ProxyClassRenderer::renderClass($type, $className);

        eval($classString);
        $instance = new $className();
        $instance::$__container_proxy_alias = $interface;

        return self::$cache[$interface] = $instance;
    }

    public static function isProxy(object $instance): bool
    {
        return \in_array($instance::class, self::$cache, true);
    }
}
