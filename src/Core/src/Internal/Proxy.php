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
        $cacheKey = \sprintf(
            '%s%s%s',
            $interface,
            $attribute->dynamicScope ? '[scoped]' : '',
            $attribute->magicCall ? '[magic-call]' : '',
        );

        if (!\array_key_exists($cacheKey, self::$cache)) {
            $n = 0;
            do {
                /** @var class-string<TClass> $className */
                $className = \sprintf(
                    '%s\%s SCOPED PROXY%s',
                    $type->getNamespaceName(),
                    $type->getShortName(),
                    $n++ > 0 ? " {$n}" : ''
                );
            } while (\class_exists($className));

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
            self::$cache[$cacheKey] = $instance;
        } else {
            /** @var TClass $instance */
            $instance = self::$cache[$cacheKey];
        }

        if ($context !== null) {
            $instance = clone $instance;
            (static fn() => $instance->__container_proxy_context = $context)->bindTo(null, $instance::class)();
        }

        return $instance;
    }
}
