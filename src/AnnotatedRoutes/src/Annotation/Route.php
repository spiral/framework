<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Router\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
final class Route
{
    public const DEFAULT_GROUP = 'default';

    /**
     * @Attribute(name="route", type="string", required=true)
     * @var string
     */
    public $route;

    /**
     * @Attribute(name="name", type="string", required=true)
     * @var string
     */
    public $name;

    /**
     * @Attribute(name="verbs", type="mixed", required=true)
     * @var mixed
     */
    public $methods = \Spiral\Router\Route::VERBS;

    /**
     * Default match options.
     *
     * @Attribute(name="defaults", type="array")
     * @var array
     */
    public $defaults = [];

    /**
     * Route group (set of middlewere), groups can be configured using MiddlewareRegistry.
     *
     * @Attribute(name="group", type="string")
     * @var string
     */
    public $group = self::DEFAULT_GROUP;

    /**
     * Route specific middleware set, if any.
     *
     * @Attribute(name="middleware", type="array")
     * @var array
     */
    public $middleware = [];
}
