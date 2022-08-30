<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

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
    public const DEFAULT_GROUP = 'default';

    /**
     * @var string
     */
    public $route;

    /**
     * @var null|string
     */
    public $name;

    /**
     * @var mixed
     */
    public $methods = \Spiral\Router\Route::VERBS;

    /**
     * Default match options.
     *
     * @var array
     */
    public $defaults = [];

    /**
     * Route group (set of middlewere), groups can be configured using MiddlewareRegistry.
     *
     * @var string
     */
    public $group = self::DEFAULT_GROUP;

    /**
     * Route specific middleware set, if any.
     *
     * @var array
     */
    public $middleware = [];

    /**
     * @var int
     */
    public $priority;

    /**
     * @psalm-param non-empty-string $route
     * @psalm-param non-empty-string|null $name
     * @psalm-param non-empty-string|array<string> $methods
     * @psalm-param non-empty-string $group
     */
    public function __construct(
        string $route,
        string $name = null,
        $methods = \Spiral\Router\Route::VERBS,
        array $defaults = [],
        string $group = self::DEFAULT_GROUP,
        array $middleware = [],
        int $priority = 0
    ) {
        $this->route = $route;
        $this->name = $name;
        $this->methods = $methods;
        $this->defaults = $defaults;
        $this->group = $group;
        $this->middleware = $middleware;
        $this->priority = $priority;
    }
}
