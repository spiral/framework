<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Router\Annotation;

use Attribute;
use Doctrine\Common\Annotations\Annotation;
use Spiral\Attributes\NamedArgumentConstructor;

/**
 * @Annotation()
 * @Annotation\Target({"METHOD"})
 * @NamedArgumentConstructor()
 */
#[Attribute(Attribute::TARGET_METHOD)]
#[NamedArgumentConstructor()]
final class Route
{
    public const DEFAULT_GROUP = 'default';

    /**
     * @Annotation\Attribute(name="route", type="string", required=true)
     * @var string
     */
    public $route;

    /**
     * @Annotation\Attribute(name="name", type="string", required=true)
     * @var string
     */
    public $name = null;

    /**
     * @Annotation\Attribute(name="verbs", type="mixed", required=true)
     * @var mixed
     */
    public $methods;

    /**
     * Default match options.
     *
     * @Annotation\Attribute(name="defaults", type="array")
     * @var array
     */
    public $defaults;

    /**
     * Route group (set of middlewere), groups can be configured using MiddlewareRegistry.
     *
     * @Annotation\Attribute(name="group", type="string")
     * @var null|string
     */
    public $group;

    /**
     * Route specific middleware set, if any.
     *
     * @Annotation\Attribute(name="middleware", type="array")
     * @var array
     */
    public $middleware;

    /**
     * @Annotation\Attribute(name="priority", type="int")
     * @var int
     */
    public $priority;

    /**
     * @param array|string $methods
     */
    public function __construct(
        string $route,
        string $name,
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
