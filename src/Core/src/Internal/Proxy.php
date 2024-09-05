<?php

declare(strict_types=1);

namespace Spiral\Core\Internal;

use Spiral\Core\ContainerScope;
use Spiral\Core\Internal\Proxy\ProxyClassRenderer;

/**
 * @internal
 */
final class Proxy
{
    /** @var array<non-empty-string, class-string> */
    private static array $classes = [];

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

        // Use the container where the proxy was created
        $attachContainer = $attribute->attachContainer;

        $cacheKey = \sprintf(
            '%s%s%s',
            $interface,
            $attachContainer ? '[attached]' : '',
            $attribute->proxyOverloads ? '[magic-calls]' : '',
        );

        if (!\array_key_exists($cacheKey, self::$classes)) {
            $n = 0;
            do {
                $className = \sprintf(
                    '%s\%s SCOPED PROXY%s',
                    $type->getNamespaceName(),
                    $type->getShortName(),
                    $n++ > 0 ? " {$n}" : ''
                );
            } while (\class_exists($className));

            /** @var class-string<TClass> $className */
            try {
                $classString = ProxyClassRenderer::renderClass(
                    $type,
                    $className,
                    $attribute->proxyOverloads,
                    $attachContainer,
                );

                eval($classString);
            } catch (\Throwable $e) {
                throw new \Error("Unable to create proxy for `{$interface}`: {$e->getMessage()}", 0, $e);
            }

            $instance = new $className();
            (static fn () => $instance::$__container_proxy_alias = $interface)->bindTo(null, $instance::class)();

            // Store in cache without context
            self::$classes[$cacheKey] = $className;
        } else {
            /** @var TClass $instance */
            $instance = new (self::$classes[$cacheKey])();
        }

        if ($context !== null || $attachContainer) {
            (static function () use ($instance, $context, $attachContainer): void {
                // Set the Current Context
                /** @see \Spiral\Core\Internal\Proxy\ProxyTrait::$__container_proxy_context */
                $context === null or $instance->__container_proxy_context = $context;

                // Set the Current Scope Container
                /** @see \Spiral\Core\Internal\Proxy\ProxyTrait::__container_proxy_container */
                $attachContainer and $instance->__container_proxy_container = ContainerScope::getContainer();
            })->bindTo(null, $instance::class)();
        }

        return $instance;
    }

    public static function isProxy(object $object): bool
    {
        return \in_array($object::class, self::$classes, true);
    }
}
