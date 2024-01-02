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
    public static function create(
        \ReflectionClass $type,
        \Stringable|string|null $context,
        \Spiral\Core\Attribute\Proxy $attribute,
    ): object {
        $interface = $type->getName();

        if (!\array_key_exists($interface, self::$cache)) {
            /** @var class-string<TClass> $className */
            $className = "{$type->getNamespaceName()}\\{$type->getShortName()} SCOPED PROXY";

            try {
                $classString = ProxyClassRenderer::renderClass($type, $className, $attribute->magicCall ? [
                    Proxy\MagicCallTrait::class,
                ] : []);

                eval($classString);
            } catch (\Throwable $e) {
                throw new \Error("Unable to create proxy for `{$interface}`: {$e->getMessage()}", 0, $e);
            }

            $instance = new $className();
            (static fn() => $instance::$__container_proxy_alias = $interface)->bindTo(null, $instance::class)();

            // Store in cache without context
            self::$cache[$interface] = $instance;
        } else {
            /** @var TClass $instance */
            $instance = self::$cache[$interface];
        }

        if ($context !== null) {
            $instance = clone $instance;
            (static fn() => $instance->__container_proxy_context = $context)->bindTo(null, $instance::class)();
        }

        return $instance;
    }
}
