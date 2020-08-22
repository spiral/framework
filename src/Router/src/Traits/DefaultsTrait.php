<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Router\Traits;

use Spiral\Router\RouteInterface;

trait DefaultsTrait
{
    /** @var array */
    protected $defaults = [];

    /**
     * Returns new route instance with forced default values.
     *
     * @param array $defaults
     * @return RouteInterface|$this
     */
    public function withDefaults(array $defaults): RouteInterface
    {
        $route = clone $this;
        $route->defaults = $defaults;

        return $route;
    }

    /**
     * Get default route values.
     *
     * @return array
     */
    public function getDefaults(): array
    {
        return $this->defaults;
    }
}
