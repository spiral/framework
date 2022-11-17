<?php

declare(strict_types=1);

namespace Spiral\Router\Annotation;

use Doctrine\Common\Annotations\Annotation\Attribute;
use Doctrine\Common\Annotations\Annotation\Attributes;
use Doctrine\Common\Annotations\Annotation\Target;
use Spiral\Attributes\NamedArgumentConstructor;

/**
 * @Annotation
 * @NamedArgumentConstructor
 * @Target({"METHOD"})
 * @Attributes({
 *     @Attribute("route", required=true, type="string"),
 *     @Attribute("name", type="string"),
 *     @Attribute("verbs", required=true, type="mixed"),
 *     @Attribute("defaults", type="array"),
 *     @Attribute("group", type="string"),
 *     @Attribute("middleware", type="array"),
 *     @Attribute("priority", type="int")
 * })
 */
#[\Attribute(\Attribute::TARGET_METHOD), NamedArgumentConstructor]
final class Route
{
    /**
     * @deprecated Deprecated since v3.3.0.
     */
    public const DEFAULT_GROUP = 'web';

    /**
     * @psalm-param non-empty-string $route
     * @psalm-param non-empty-string|null $name
     * @psalm-param non-empty-string|array<string> $methods
     * @psalm-param non-empty-string $group Route group, groups can be configured using MiddlewareRegistry
     * @param array $middleware Route specific middleware set, if any
     */
    public function __construct(
        public readonly string $route,
        public readonly ?string $name = null,
        public readonly array|string $methods = \Spiral\Router\Route::VERBS,
        public readonly array $defaults = [],
        public readonly ?string $group = null,
        public readonly array $middleware = [],
        public readonly int $priority = 0
    ) {
    }
}
